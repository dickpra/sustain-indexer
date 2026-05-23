<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Author;
use App\Models\Institution;


class BetaSubmitController extends Controller
{
    // Tampilkan form upload awal
    public function create()
    {
        // Gunakan view eksperimen (nanti kamu tinggal copy-paste view submit_xml jadi submit_beta)
        return view('submit_beta'); 
    }

    // ==========================================
    // THE MAGIC HYBRID SCANNER
    // ==========================================
    // ==========================================
    // THE MAGIC HYBRID SCANNER (ANTI-BIBLIOGRAPHY TRAP)
    // ==========================================
    // ==========================================
    // THE MAGIC HYBRID SCANNER (REGEX DOI + AI METADATA)
    // ==========================================
    public function scanPdfHybrid(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240', 
        ]);

        $path = $request->file('pdf_file')->store('temp');
        $fullPath = storage_path('app/' . $path);
        
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fullPath);
        
        $fullTextForDoi = $pdf->getText();
        
        $pages = $pdf->getPages();
        $textToAnalyze = '';
        $maxPages = min(2, count($pages)); 
        for ($i = 0; $i < $maxPages; $i++) {
            $textToAnalyze .= $pages[$i]->getText() . "\n";
        }

        \Illuminate\Support\Facades\Storage::delete($path);

        if (empty(trim($textToAnalyze))) {
            return back()->with('error', 'PDF is unreadable or is a scanned image.');
        }

        $extractedData = [];
        $doiFound = null;

        // =======================================================
        // 1. TUGAS PLUGIN: BERBURU DOI PAKAI REGEX
        // =======================================================
        $doiPattern = '/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i';
        
        // Prioritas 1: Cari di 2 halaman awal dulu (Paling aman dari daftar pustaka)
        if (preg_match($doiPattern, $textToAnalyze, $matches)) {
            $doiFound = rtrim($matches[0], '.,;');
        } 
        // Prioritas 2: Kalau di awal kosong, baru cari di seluruh PDF
        elseif (preg_match($doiPattern, $fullTextForDoi, $matches)) {
            $doiFound = rtrim($matches[0], '.,;');
        }

        // =======================================================
        // 2. CEK CROSSREF (JIKA DOI KETEMU)
        // =======================================================
        if ($doiFound) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://api.crossref.org/works/' . $doiFound);
                
                if ($response->successful()) {
                    $crossref = $response->json('message');
                    $crossrefTitle = $crossref['title'][0] ?? '';
                    
                    // Validasi: Apakah judul dari API cocok dengan teks di PDF?
                    $cleanCrossrefTitle = preg_replace('/[^a-z0-9]/i', '', strtolower($crossrefTitle));
                    $cleanPdfText = preg_replace('/[^a-z0-9]/i', '', strtolower($textToAnalyze));
                    
                    if (str_contains($cleanPdfText, substr($cleanCrossrefTitle, 0, 20))) {
                        $extractedData['title'] = $crossrefTitle;
                        $extractedData['abstract'] = strip_tags($crossref['abstract'] ?? '');
                        $extractedData['doi'] = 'https://doi.org/' . $doiFound;
                        $extractedData['pub_year'] = $crossref['published-print']['date-parts'][0][0] ?? date('Y');
                        $extractedData['journal_title'] = $crossref['container-title'][0] ?? '';
                        $extractedData['publisher'] = $crossref['publisher'] ?? '';
                        
                        $authors = [];
                        if (isset($crossref['author'])) {
                            foreach ($crossref['author'] as $auth) {
                                $rawInst = $auth['affiliation'][0]['name'] ?? '';
                                $cleanInst = $rawInst;
                                if (!empty($rawInst)) {
                                    $parts = array_map('trim', explode(',', $rawInst));
                                    $univKeywords = ['university', 'universitas', 'institut', 'polytechnic', 'college', 'academy', 'school', 'universidad', 'université', 'università', 'universiteit', 'universität'];
                                    foreach ($parts as $part) {
                                        foreach ($univKeywords as $kw) {
                                            if (stripos($part, $kw) !== false) {
                                                $cleanInst = $part; 
                                                break 2;
                                            }
                                        }
                                    }
                                }
                                $authors[] = [
                                    'name' => ($auth['given'] ?? '') . ' ' . ($auth['family'] ?? ''),
                                    'email' => '', 
                                    'institution' => $cleanInst, 
                                    'country' => ''
                                ];
                            }
                        }
                        $extractedData['authors'] = $authors;
                        $extractedData['source'] = 'Crossref API'; 
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Crossref gagal: " . $doiFound);
            }
        }

        // =======================================================
        // 3. TUGAS AI: BACA METADATA (TANPA MIKIRIN DOI)
        // =======================================================
        if (empty($extractedData)) {
            try {
                $groqResponse = \Illuminate\Support\Facades\Http::withToken(env('GROQ_API_KEY'))
                    ->timeout(15)
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => 'meta-llama/llama-4-scout-17b-16e-instruct', // Model Llama 3 yang super cepat
                        'response_format' => ['type' => 'json_object'], 
                        'messages' => [
                            [
                                'role' => 'system', 
                                // 🔥 KITA HAPUS "DOI" DARI TUGAS AI 🔥
                                'content' => 'You are an academic extractor. Extract metadata and return exactly this JSON: {"title": "str", "abstract": "str", "authors": [{"name": "str", "email": "str", "institution": "str", "country": "str"}], "keywords": "str", "journal_title": "str", "pub_year": 2024}. STRICT RULE FOR "institution": Extract ONLY primary university name, REMOVE departments/faculties.'
                            ],
                            [
                                'role' => 'user', 
                                'content' => "Extract from this text:\n\n" . substr($textToAnalyze, 0, 15000)
                            ]
                        ]
                    ]);

                if ($groqResponse->successful()) {
                    $aiContent = json_decode($groqResponse->json('choices.0.message.content'), true);
                    
                    $extractedData = [
                        'title' => $aiContent['title'] ?? '',
                        'abstract' => $aiContent['abstract'] ?? '',
                        
                        // 🔥 KITA GABUNGKAN KERJAAN PLUGIN DAN AI DI SINI 🔥
                        'doi' => $doiFound ? 'https://doi.org/' . $doiFound : '',
                        
                        'pub_year' => $aiContent['pub_year'] ?? date('Y'),
                        'journal_title' => $aiContent['journal_title'] ?? '',
                        'publisher' => '', 
                        'keywords' => $aiContent['keywords'] ?? '',
                        'authors' => $aiContent['authors'] ?? [],
                        'source' => 'Groq AI'
                    ];
                } else {
                    return back()->with('error', 'Groq API Error: ' . $groqResponse->body());
                }
            } catch (\Exception $e) {
                return back()->with('error', 'AI server timeout. Please fill manually.');
            }
        }

        // =======================================================
        // 4. BENTENG DUPLIKASI, KEYWORD, DAN PETA
        // =======================================================
        $titleToCheck = $extractedData['title'] ?? '';
        if (!empty($titleToCheck)) {
            $existingDoc = \App\Models\Document::where('title', $titleToCheck)->first();
            if ($existingDoc) {
                if (!$existingDoc->is_verified) {
                    return redirect('/receipt/' . $existingDoc->document_number)->with('error', 'Document awaiting verification.');
                } else {
                    return redirect('/submit-beta')->with('error', 'System Rejection: Document officially indexed.');
                }
            }
        }

        if (!empty($extractedData['keywords'])) {
            $cleanKw = str_replace(';', ',', $extractedData['keywords']);
            $extractedData['keywords'] = trim(preg_replace('/\s*,\s*/', ', ', $cleanKw), ', ');
        }

        if (!empty($extractedData['authors'])) {
            foreach ($extractedData['authors'] as &$author) {
                $author['lat'] = '';
                $author['lng'] = '';
                if (!empty($author['institution'])) {
                    $existingInst = \App\Models\Institution::where('name', 'like', '%' . $author['institution'] . '%')->whereNotNull('latitude')->first();
                    if ($existingInst) {
                        $author['lat'] = $existingInst->latitude;
                        $author['lng'] = $existingInst->longitude;
                    } else {
                        try {
                            $parts = explode(',', $author['institution']);
                            $searchQuery = $author['institution']; 
                            foreach($parts as $part) {
                                if (preg_match('/(universitas|institut|politeknik|academy|college|school|university)/i', $part)) {
                                    $searchQuery = trim($part); break;
                                }
                            }
                            $response = \Illuminate\Support\Facades\Http::withHeaders(['User-Agent' => 'SustainDex/1.0'])->timeout(3)->get('https://nominatim.openstreetmap.org/search', ['q' => $searchQuery, 'format' => 'json', 'limit' => 1]);
                            $geo = $response->json();
                            if (!empty($geo)) {
                                $author['lat'] = $geo[0]['lat'];
                                $author['lng'] = $geo[0]['lon'];
                            }
                        } catch (\Exception $e) {}
                    }
                }
            }
        }

        return view('submit_beta', compact('extractedData'));
    }

    // Fungsi Finalize (Tinggal copy paste fungsi storeXmlFinal kamu dari DocumentController, lalu sesuaikan redaksinya)
    // ==========================================
    // THE FINAL SAVER (Tanpa Scan Ulang PDF)
    // ==========================================
    // ==========================================
    // THE FINAL SAVER (Dengan Mesin Pencuri Sitasi)
    // ==========================================
    public function storeFinal(Request $request)
    {
        // 1. Validasi input dari form review (Tanpa pdf_file)
        $request->validate([
            'title' => 'required|string',
            'abstract' => 'required|string',
            'document_type' => 'required|string',
            'submitter_first_name' => 'required|string',
            'submitter_last_name' => 'required|string',
            'submitter_email' => 'required|email',
            'authors' => 'required|array|min:1',
            'authors.*.name' => 'required|string',
            'authors.*.email' => 'nullable|email', 
        ]);

        // 2. Benteng Duplikasi Final
        $existingDoc = \App\Models\Document::where('title', $request->title)->first();
        if ($existingDoc) {
            if (!$existingDoc->is_verified) {
                return redirect('/receipt/' . $existingDoc->document_number)
                    ->with('error', 'This document title has already been submitted and is awaiting email verification.');
            } else {
                return redirect('/submit-beta')
                    ->with('error', 'System Rejection: Document with title "' . $request->title . '" has already been officially indexed.');
            }
        }

        // =======================================================
        // 🔥 JURUS SITASI CROSSREF (ANTI-SSL BLOCK & TIMEOUT) 🔥
        // =======================================================
        $doi = null;
        $citationCount = 0; // Default 0
        
        if (!empty($request->doi)) {
            $doiPattern = '/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i';
            
            if (preg_match($doiPattern, $request->doi, $matches)) {
                $rawDoi = $matches[0]; 
                $doi = 'https://doi.org/' . $rawDoi; 

                try {
                    // 🔥 PERBAIKAN 1: withoutVerifying() untuk bypass SSL localhost
                    // 🔥 PERBAIKAN 2: timeout(10) biar gak keburu diputus
                    $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                                    ->timeout(10)
                                    ->get('https://api.crossref.org/works/' . $rawDoi);
                    
                    if ($response->successful()) {
                        $citationCount = $response->json('message.is-referenced-by-count') ?? 0;
                        
                        // CCTV LOG: Biar kita tahu sukses apa enggak!
                        \Illuminate\Support\Facades\Log::info("✅ BERHASIL CROSSREF! DOI: {$rawDoi} | Sitasi: {$citationCount}");
                    } else {
                        // CCTV LOG: Kalau Crossref nolak (Error 404/403)
                        \Illuminate\Support\Facades\Log::error("❌ CROSSREF NOLAK! Status: " . $response->status());
                    }
                } catch (\Exception $e) {
                    // CCTV LOG: Kalau server down atau koneksi putus
                    \Illuminate\Support\Facades\Log::error("⚠️ CROSSREF EXCEPTION: " . $e->getMessage());
                }
            } else {
                $doi = $request->doi; 
            }
        } else {
            \Illuminate\Support\Facades\Log::warning("⚠️ DOI KOSONG DARI FORM, SITASI DI-SKIP!");
        }
        // =======================================================
        // =======================================================

        // 3. Simpan ke Tabel Documents
        $docNumber = 'IDX-' . rand(100000, 999999); // 🔥 UBAH JADI IDX- + 6 ANGKA ACAK 🔥

        $document = \App\Models\Document::create([
            'document_number' => $docNumber,
            'title' => $request->title,
            'journal_title' => $request->journal_title, 
            'publisher' => $request->publisher,
            'abstract' => $request->abstract,
            'document_type' => $request->document_type, 
            'pub_year' => $request->pub_year,
            'doi' => $doi, 
            'keywords' => $request->keywords, 
            'pages' => $request->pages, 
            'reference_count' => $request->reference_count, 
            'is_verified' => false, 
            'views' => 0,
            'citation_count' => $citationCount, 
            'submitter_first_name' => $request->submitter_first_name,
            'submitter_last_name' => $request->submitter_last_name,
            'submitter_email' => $request->submitter_email,
            'verification_token' => \Illuminate\Support\Str::random(40),
        ]);

        // 4. Simpan Authors & Institusi (Pivot Table)
        if ($request->has('authors')) {
            $authorIdsToAttach = [];

            foreach ($request->authors as $authorData) {
                $institutionId = null;
                
                if (!empty($authorData['institution'])) {
                    $institution = \App\Models\Institution::firstOrCreate(
                        ['name' => $authorData['institution']],
                        ['country' => $authorData['country'] ?? 'Unknown']
                    );
                    $institutionId = $institution->id;

                    // Fitur Admin Siluman: Update Koordinat Map
                    $isManualOverride = isset($authorData['manual_map']) && $authorData['manual_map'] == "1";
                    $isEmptyInDB = !$institution->latitude && isset($authorData['lat']);

                    if (($isEmptyInDB || $isManualOverride) && isset($authorData['lat'])) {
                        $institution->update([
                            'latitude' => $authorData['lat'],
                            'longitude' => $authorData['lng'],
                            'country' => $authorData['country'] ?? $institution->country
                        ]);
                    }
                }

                // Logika Deduplikasi Cerdas
                $email = !empty($authorData['email']) ? $authorData['email'] : null;

                if ($email) {
                    $author = \App\Models\Author::firstOrCreate(
                        ['email' => $email], 
                        [
                            'name' => $authorData['name'],
                            'institution_id' => $institutionId,
                            'country' => $authorData['country'] ?? 'Unknown',
                        ]
                    );
                } else {
                    $author = \App\Models\Author::firstOrCreate(
                        ['name' => $authorData['name']], 
                        [
                            'email' => null,
                            'institution_id' => $institutionId,
                            'country' => $authorData['country'] ?? 'Unknown',
                        ]
                    );
                }

                $authorIdsToAttach[] = $author->id; 
            }

            $document->authors()->attach($authorIdsToAttach);
        }

        // 5. Kirim Email Verifikasi
        dispatch(function () use ($document) {
            \Illuminate\Support\Facades\Mail::to($document->submitter_email)
                ->send(new \App\Mail\VerifyDocumentEmail($document));
        })->afterResponse();

        // 6. Redirect ke Halaman Tanda Terima
        return redirect('/receipt/' . $document->document_number)->with('success', 'AI Extracted data saved! Please check your email to verify and activate the index.');
    }
}