<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyDocumentEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Author;
use App\Models\Institution;

class DocumentController extends Controller
{
    // ==========================================
    // 1. Fungsi Menerima Data dari Form Submit
    // ==========================================
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'submitter_first_name' => 'required|string',
            'submitter_last_name' => 'required|string',
            'submitter_email' => 'required|email',
            'title' => 'required|string',
            'abstract' => 'required|string',
            'keywords' => 'nullable|string',
            'document_type' => 'required|string',
            'pub_year' => 'nullable|integer',
            'pages' => 'nullable|integer',
            'reference_count' => 'nullable|integer',
            'pdf_file' => 'required|mimes:pdf|max:102400',
            'authors' => 'required|array|min:1', 
            'authors.*.name' => 'required|string', 
            'authors.*.email' => 'required|email',
            'authors.*.country' => 'nullable|string',
            'authors.*.institution' => 'required|string',
            'authors.*.lat' => 'nullable|numeric',
            'authors.*.lng' => 'nullable|numeric',
        ]);
        // ==========================================
        // FITUR BARU: CEK DUPLIKASI JUDUL DARI DATABASE
        // ==========================================
        $existingDoc = Document::where('title', $request->title)->first();

        if ($existingDoc) {
            if (!$existingDoc->is_verified) {
                // KONDISI 1: Ada tapi BELUM diverifikasi (Pending)
                // Arahkan user ke halaman Receipt yang sudah ada
                return response()->json([
                    'status' => 'pending_duplicate',
                    'message' => 'A document with this title has already been submitted and is awaiting email verification.',
                    'confirmation_id' => $existingDoc->document_number
                ], 200); 
            } else {
                // KONDISI 2: Ada dan SUDAH diverifikasi (Published)
                // Tolak keras-keras!
                return response()->json([
                    'error' => 'System Rejection: Document with title "' . $request->title . '" has already been officially indexed in our database.'
                ], 422);
            }
        }
        // ==========================================

        // 2. Simpan & Ekstrak PDF
        $path = $request->file('pdf_file')->store('temp');
        $fullPath = storage_path('app/' . $path);
        
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fullPath);
        $rawText = $pdf->getText();

        // 3. NORMALISASI TEKS (JURUS SPASI HILANG)
        // Kita hapus SEMUA spasi, enter, dan simbol. Hanya sisakan huruf murni dan angka!
        $superCleanPdfText = preg_replace('/[^a-z0-9]/i', '', strtolower($rawText));
        $superCleanInputTitle = preg_replace('/[^a-z0-9]/i', '', strtolower($request->title));

        // [SAFETY NET] Cek apakah PDF bisa dibaca (antisipasi PDF hasil scan)
        if (empty($superCleanPdfText)) {
            \Illuminate\Support\Facades\Storage::delete($path);
            return response()->json([
                'error' => 'System Rejection: The uploaded PDF file appears to be unreadable or is likely a scanned image. Please ensure you upload a text-based PDF document.'
            ], 422);
        }

        // 4. DUAL VALIDATION: Cek Judul Tanpa Spasi
        if (!str_contains($superCleanPdfText, $superCleanInputTitle)) {
            \Illuminate\Support\Facades\Storage::delete($path);
            
            // DEBUGGING: Kita keluarkan potongan teksnya di error biar ketahuan apa yang dibaca mesin!
            $pdfSnippet = substr($superCleanPdfText, 0, 50) . '...';
            return response()->json([
                'error' => 'Validation Rejected: The title was not found exactly in the PDF file. (System read: ' . $pdfSnippet . ')'
            ], 422);
        }

        // 5. DUAL VALIDATION: Cek SEMUA Author Tanpa Spasi
        foreach ($request->authors as $authorData) {
            $superCleanAuthor = preg_replace('/[^a-z0-9]/i', '', strtolower($authorData['name']));
            
            if (!str_contains($superCleanPdfText, $superCleanAuthor)) {
                \Illuminate\Support\Facades\Storage::delete($path);
                return response()->json([
                    'error' => 'Validation Rejected: Author name ("' . $authorData['name'] . '") was not found in the PDF file.'
                ], 422);
            }
        }

        // =======================================================
        // 6. EKSTRAKSI DOI & AUTO-FETCH SITASI (CROSSREF API)
        // =======================================================
        $doi = null;
        $citationCount = 0; // Default jumlah sitasi adalah 0
        
        $doiPattern = '/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i';
        
        if (preg_match($doiPattern, $rawText, $matches)) {
            $rawDoi = $matches[0]; // Format asli: 10.xxxx/yyyy
            $doi = 'https://doi.org/' . $rawDoi; // Format Link URL

            // 🔥 Kita tembak API Crossref diem-diem buat nyuri data sitasinya!
            try {
                // Timeout 5 detik biar kalau Crossref lemot, webmu gak ikutan hang
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://api.crossref.org/works/' . $rawDoi);
                
                if ($response->successful()) {
                    // Ambil angka sitasi dari JSON balasan Crossref
                    $citationCount = $response->json('message.is-referenced-by-count') ?? 0;
                }
            } catch (\Exception $e) {
                // Kalau API gagal/down, diamkan saja (tetap 0). Jangan ganggu proses submit.
                \Illuminate\Support\Facades\Log::warning("Gagal ambil sitasi Crossref untuk DOI: " . $rawDoi);
            }
        }
        // =======================================================

        // =========================================================
        // 7. TRANSAKSI DATABASE (ANTI-GAGAL & DEDUPLIKASI)
        // =========================================================
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $authorIdsToAttach = [];

            // A. Looping Pengecekan Institusi & Author (ANTI-GHOST AUTHOR)
            foreach ($request->authors as $authorData) {
                
                // Cek Institusi: Kalau namanya sudah ada, pakai ID yang lama. Kalau belum, buat baru.
                $institution = Institution::firstOrCreate(
                    ['name' => $authorData['institution']], 
                    [
                        'country' => $authorData['country'] ?? null,
                        'latitude' => $authorData['lat'] ?? null,    // <-- Masukkan Lat
                        'longitude' => $authorData['lng'] ?? null    // <-- Masukkan Lng
                    ] 
                );

                // FITUR CERDAS: Kalau Institusi lama sudah ada tapi koordinat petanya masih kosong, update sekalian!
                if (!$institution->wasRecentlyCreated && !$institution->latitude && isset($authorData['lat'])) {
                    $institution->update([
                        'latitude' => $authorData['lat'],
                        'longitude' => $authorData['lng']
                    ]);
                }

                // Cek Author: Ditelusuri berdasarkan EMAIL!
                $author = Author::firstOrCreate(
                    ['email' => $authorData['email']], 
                    [
                        'name' => $authorData['name'],
                        'country' => $authorData['country'] ?? null,
                        'institution_id' => $institution->id
                    ]
                );

                // Kumpulkan ID Author untuk ditautkan ke Jurnal
                $authorIdsToAttach[] = $author->id;
            }

            // B. Siapkan Data Dokumen Utama
            $docNumber = 'IDX-' . rand(100000, 999999);
            $token = \Illuminate\Support\Str::random(40);

            $document = Document::create([
                'document_number' => $docNumber,
                'title' => $request->title,
                'journal_title' => $request->journal_title, // <-- Tambahan baru
                'publisher' => $request->publisher,
                'abstract' => $request->abstract,
                'keywords' => $request->keywords,
                'document_type' => $request->document_type,
                'pub_year' => $request->pub_year,
                'pages' => $request->pages,                     
                'reference_count' => $request->reference_count, 
                'doi' => $doi,
                'citation_count' => $citationCount,
                'verification_token' => $token,
                'submitter_first_name' => $request->submitter_first_name, 
                'submitter_last_name' => $request->submitter_last_name,   
                'submitter_email' => $request->submitter_email,           
                'is_peer_reviewed' => true, 
            ]);

            // =========================================================
            // 🔥 FITUR BARU: LANGSUNG CATAT KE TABEL HISTORY SAAT SUBMIT
            // =========================================================
            \Illuminate\Support\Facades\DB::table('citation_histories')->insert([
                'document_id' => $document->id,
                'citation_count' => $citationCount, // Angka dari Crossref saat scan
                'year' => date('Y'),
                'month' => date('m'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // C. RELASI PIVOT: Sambungkan Dokumen dengan Author-Author
            $document->authors()->attach($authorIdsToAttach);

            // D. Kirim Email Verifikasi Instan (After Response)
            dispatch(function () use ($document) {
                \Illuminate\Support\Facades\Mail::to($document->submitter_email)
                    ->send(new \App\Mail\VerifyDocumentEmail($document));
            })->afterResponse();

            // E. Resmikan Data!
            \Illuminate\Support\Facades\DB::commit();
            \Illuminate\Support\Facades\Storage::delete($path);

            return response()->json([
                'status' => 'success',
                'confirmation_id' => $docNumber
            ], 200);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Storage::delete($path);
            \Illuminate\Support\Facades\Log::error('SustainDex Submit Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'System failed to process the document: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==========================================
    // 2. Fungsi Verifikasi Email via Link
    // ==========================================
    public function verifyEmail($token)
    {
        $document = Document::where('verification_token', $token)->first();

        if (!$document) {
            return "Verification link is invalid or has expired.";
        }

        $document->update([
            'is_verified' => true,
            'verification_token' => null
        ]);

        return "Congratulations! Your document has been successfully verified and now it is available in the search system.";
    }

    // ==========================================
    // 3. Fungsi Halaman Utama (Index)
    // ==========================================
    public function index()
    {
        // ==========================================
        // 1. DATA SIDEBAR: TIPE DOKUMEN
        // ==========================================
        $docTypes = \App\Models\Document::where('is_verified', true)
                        ->select('document_type', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                        ->groupBy('document_type')
                        ->get();

        // ==========================================
        // 2. DATA SIDEBAR: STATISTIK TAHUN ALA SCHOLAR
        // ==========================================
        $currentYear = date('Y');
        
        $yearStats = [
            'current_year' => $currentYear,
            'count_current' => \App\Models\Document::where('is_verified', true)->where('pub_year', $currentYear)->count(),
            
            'last_year' => $currentYear - 1,
            'count_last' => \App\Models\Document::where('is_verified', true)->where('pub_year', '>=', $currentYear - 1)->count(),
            
            'year_5' => $currentYear - 4,
            'count_5' => \App\Models\Document::where('is_verified', true)->where('pub_year', '>=', $currentYear - 4)->count(),
            
            'year_10' => $currentYear - 9,
            'count_10' => \App\Models\Document::where('is_verified', true)->where('pub_year', '>=', $currentYear - 9)->count(),
            
            'year_20' => $currentYear - 19,
            'count_20' => \App\Models\Document::where('is_verified', true)->where('pub_year', '>=', $currentYear - 19)->count(),
        ];

        // ==========================================
        // 3. FITUR BARU: MOST CITED & MOST POPULAR
        // ==========================================
        $mostCited = \App\Models\Document::where('is_verified', true)
                     ->orderBy('citation_count', 'desc')
                     ->take(3)
                     ->get();

        $mostPopular = \App\Models\Document::where('is_verified', true)
                       ->orderBy('views', 'desc')
                       ->take(3)
                       ->get();

        // ==========================================
        // 4. LEMPAR SEMUA VARIABEL KE VIEW INDEX
        // ==========================================
        return view('index', compact('docTypes', 'yearStats', 'mostCited', 'mostPopular'));
    }

    // ==========================================
    // 4. Fungsi Pencarian (Search API) - UPDATED FACETED
    // ==========================================
    public function search(Request $request)
    {
        $q = $request->query('q');
        $type = $request->query('type');
        $year = $request->query('year');
        $authorFilter = $request->query('author');
        
        // 1. QUERY DASAR (Hanya berdasarkan teks pencarian)
        // 1. QUERY DASAR (Hanya berdasarkan teks pencarian)
        $baseQuery = Document::with('authors.institution')->where('is_verified', true);

        if ($q) {
            $baseQuery->where(function($queryBuilder) use ($q) {
                $queryBuilder->where('title', 'like', "%$q%")
                             ->orWhere('abstract', 'like', "%$q%")
                             ->orWhere('keywords', 'like', "%$q%") // <--- TAMBAHKAN BARIS INI BOS! 🔥
                             ->orWhere('document_number', 'like', "%$q%")
                             ->orWhereHas('authors', function($authorQuery) use ($q) {
                                 $authorQuery->where('name', 'like', "%$q%");
                             });
            });
        }

        if ($authorFilter) {
            $baseQuery->whereHas('authors', function($authorQuery) use ($authorFilter) {
                $authorQuery->where('name', 'like', "%{$authorFilter}%");
            });
        }

        // 2. HITUNG ULANG ANGKA SIDEBAR (FACETS) BERDASARKAN PENCARIAN
        // Hitung Tipe Jurnal
        $typeFacets = (clone $baseQuery)
            ->select('document_type', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('document_type')
            ->pluck('total', 'document_type'); // Menghasilkan: ['Journal' => 5, 'Book' => 2]

        // Hitung Tahun
        $currentYear = date('Y');
        $yearFacets = [
            'count_current' => (clone $baseQuery)->where('pub_year', $currentYear)->count(),
            'count_last'    => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 1)->count(),
            'count_5'       => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 4)->count(),
            'count_10'      => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 9)->count(),
            'count_20'      => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 19)->count(),
        ];

        // 3. TERAPKAN FILTER SAMPING UNTUK MENAMPILKAN HASIL JURNAL
        $query = clone $baseQuery;
        
        if ($type) {
            $query->where('document_type', $type);
        }

        if ($year) {
            if (str_starts_with($year, 'exact_')) {
                $exactYear = str_replace('exact_', '', $year);
                $query->where('pub_year', $exactYear);
            } elseif (str_starts_with($year, 'since_')) {
                $sinceYear = str_replace('since_', '', $year);
                $query->where('pub_year', '>=', $sinceYear);
            }
        }

        // 4. HASIL AKHIR
        $results = $query->orderBy('pub_year', 'desc')->latest()->paginate(10);

        // Gabungkan hasil jurnal dengan angka sidebar baru!
        $response = $results->toArray();
        $response['facets'] = [
            'types' => $typeFacets,
            'years' => $yearFacets
        ];

        return response()->json($response);
    }
   // ==========================================
    // 5. Fungsi Halaman Detail (Show)
    // ==========================================
    public function show($id)
    {
        // 1. Tarik dokumennya saja
        $document = Document::where('document_number', $id)->firstOrFail();

        // 2. Tambah views
        $document->increment('views');

        // 3. Tarik relasi author
        $authors = $document->authors()->with('institution')->get();

        // ==========================================
        // 🔥 AMBIL DATA DARI TABEL SITASI BARU
        // Cari kapan terakhir kali dokumen ini di-sync oleh robot
        // ==========================================
        $latestCitation = \Illuminate\Support\Facades\DB::table('citation_histories')
                            ->where('document_id', $document->id)
                            ->latest('created_at')
                            ->first();

        // Lempar semuanya ke view
        return view('show', compact('document', 'authors', 'latestCitation'));
    }

    // ==========================================
    // 6. Fungsi Halaman Tanda Terima (Receipt)
    // ==========================================
    public function receipt($id)
    {
        $document = Document::where('document_number', $id)->firstOrFail();
        
        return view('receipt', compact('document'));
    }

    // ==========================================
    // 7. Fungsi Kirim Ulang Email (Resend)
    // ==========================================
    public function resendEmail(Request $request)
    {
        $request->validate(['document_number' => 'required|string']);
        
        $document = Document::where('document_number', $request->document_number)->first();

        if (!$document) {
            return response()->json(['error' => 'Document not found.'], 404);
        }

        if ($document->is_verified) {
            return response()->json(['error' => 'This document has already been verified and is available in the search system.'], 400);
        }

        dispatch(function () use ($document) {
            \Illuminate\Support\Facades\Mail::to($document->submitter_email)
                ->send(new \App\Mail\VerifyDocumentEmail($document));
        })->afterResponse();

        return response()->json(['message' => 'Verification email has been resent successfully! Please check your inbox or spam folder.']);
    }

    // ==========================================
    // 8. Fungsi API Live Search Institusi
    // ==========================================
    public function searchInstitutions(Request $request)
    {
        $search = $request->query('q');
        
        // Kalau kosong, kembalikan array kosong
        if (!$search) {
            return response()->json([]);
        }

        // Cari 10 kampus teratas yang namanya mirip dengan ketikan user
        $institutions = \App\Models\Institution::where('name', 'like', "%{$search}%")
                        ->limit(10)
                        ->get(['id', 'name', 'latitude', 'longitude']);
        
        return response()->json($institutions);
    }

    public function showAuthor(Request $request, $id)
    {
        $author = Author::with('institution')->findOrFail($id);

        // Kalau yang minta data adalah Javascript (Fetch)
        if ($request->wantsJson() || $request->ajax()) {
            $documents = $author->documents()
                        ->where('is_verified', true)
                        ->latest()
                        ->paginate(10);
            return response()->json($documents);
        }

        // Ambil ID semua dokumen milik Author ini
        $documentIds = $author->documents->pluck('id');

        // Cari riwayat sitasi tertinggi per tahun untuk author ini
        $citationChartData = DB::table('citation_histories')
            ->select('year', DB::raw('MAX(citation_count) as total_citations'))
            ->whereIn('document_id', $documentIds)
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get();

        // =========================================================
        // 🔥 JARING PENGAMAN: JIKA TABEL MASIH KOSONG 🔥
        // =========================================================
        if ($citationChartData->isEmpty()) {
            // Beri data dummy 3 tahun terakhir dengan nilai 0
            $chartYears = json_encode([date('Y') - 2, date('Y') - 1, date('Y')]); 
            $chartCounts = json_encode([0, 0, 0]);
        } else {
            // Kalau sudah ada isinya, pakai data asli
            $chartYears = $citationChartData->pluck('year')->toJson();
            $chartCounts = $citationChartData->pluck('total_citations')->toJson();
        }

        return view('author_profile', compact('author', 'chartYears', 'chartCounts'));
    }

    public function showInstitution(Request $request, $id)
    {
        // 1. Cari data kampusnya
        $institution = \App\Models\Institution::findOrFail($id);

        // ==========================================
        // TAMBAHKAN KODE INI SEMENTARA UNTUK CEK DB
        // dd($institution->toArray()); 
        // ==========================================

        // 2. Jika dipanggil oleh Javascript (Fetch Pagination)
        if ($request->wantsJson() || $request->ajax()) {
            // Tarik author kampus ini, hitung jurnalnya, lalu urutkan dari yang terbanyak
            $authors = $institution->authors()
                ->withCount(['documents' => function ($query) {
                    $query->where('is_verified', true);
                }])
                ->orderBy('documents_count', 'desc')
                ->paginate(12); // Menampilkan 12 Author per halaman (biar pas untuk Grid 3 kolom)
                
            return response()->json($authors);
        }

        // 3. Jika di-load pertama kali oleh Browser
        // Hitung total seluruh jurnal dari kampus ini untuk dipajang di header
        $totalDocuments = \App\Models\Document::whereHas('authors', function($query) use ($id) {
            $query->where('institution_id', $id);
        })->where('is_verified', true)->count();

        // Lempar ke tampilan HTML
        return view('institution_profile', compact('institution', 'totalDocuments'));
    }


    // ==========================================
    // 1. Tampilkan Halaman Awal Form XML
    // ==========================================
    public function createXml()
    {
        return view('submit_xml');
    }

    // ==========================================
    // 2. Proses Pindai (Scan) XML & Lempar ke Form Review
    // ==========================================
    public function scanXml(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'ojs_xml' => 'required|file|mimes:xml|max:10240',
        ]);

        $xmlString = file_get_contents($request->file('ojs_xml')->getRealPath());
        
        // Bersihkan Namespace
        $xmlString = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlString);
        $xmlString = preg_replace('/[a-zA-Z]+:([a-zA-Z]+[=>])/', '$1', $xmlString);
        $xml = simplexml_load_string($xmlString);
        
        $articles = $xml->xpath('//record | //article');

        if (!$articles) {
            return back()->with('error', 'Format XML tidak dikenali. Pastikan file menggunakan standar OJS atau DOAJ.');
        }

        $article = $articles[0]; 
        $title = (string) $article->title;

        // =======================================================
        // 🔥 DETEKSI DINI: CEK DUPLIKASI JUDUL SAAT BARU SCAN XML
        // =======================================================
        $existingDoc = Document::where('title', $title)->first();

        if ($existingDoc) {
            if (!$existingDoc->is_verified) {
                // KONDISI 1: Belum Verifikasi -> Oper ke halaman receipt-nya langsung
                return redirect('/receipt/' . $existingDoc->document_number)
                    ->with('error', 'This document title has already been submitted and is awaiting email verification. We have redirected you to its receipt page.');
            } else {
                // KONDISI 2: Sudah Terindeks -> Blokir total!
                return back()->with('error', 'System Rejection: Document with title "' . $title . '" has already been officially indexed in our database.');
            }
        }
        // =======================================================
        
        // Ekstrak DOI 
        $doi = '';
        if (isset($article->doi)) {
            $doi = (string) $article->doi;
        } elseif (isset($article->id)) {
            foreach ($article->id as $idNode) {
                if ((string)$idNode['type'] == 'doi') {
                    $doi = (string) $idNode;
                    break;
                }
            }
        }

        // Ekstrak Tahun Terbit
        $pubYear = date('Y');
        if (isset($article->publicationDate)) {
            $pubYear = date('Y', strtotime((string)$article->publicationDate));
        } elseif (isset($article->date_published)) {
            $pubYear = date('Y', strtotime((string)$article->date_published));
        }

        // Mapping Afiliasi/Kampus ala DOAJ
        $affiliations = [];
        if (isset($article->affiliationsList->affiliationName)) {
            foreach ($article->affiliationsList->affiliationName as $affil) {
                $id = (string) $affil['affiliationId'];
                $affiliations[$id] = (string) $affil;
            }
        }

        // Ekstrak Authors
        $authorsList = [];
        if (isset($article->authors->author)) {
            foreach ($article->authors->author as $authorNode) {
                if (isset($authorNode->name)) {
                    $name = (string) $authorNode->name;
                } else {
                    $name = trim((string)$authorNode->givenname . ' ' . (string)$authorNode->familyname);
                }

                $email = isset($authorNode->email) ? (string) $authorNode->email : '';

                $institution = '';
                if (isset($authorNode->affiliationId)) {
                    $affilId = (string) $authorNode->affiliationId;
                    $institution = $affiliations[$affilId] ?? '';
                } elseif (isset($authorNode->affiliation)) {
                    $institution = (string) $authorNode->affiliation;
                }

                $authorsList[] = [
                    'name' => $name,
                    'email' => $email,
                    'institution' => $institution,
                    'country' => (string) ($authorNode->country ?? 'Unknown')
                ];
            }
        }

        // Ekstrak Keywords
        $keywordsArr = [];
        if (isset($article->keywords->keyword)) {
            foreach ($article->keywords->keyword as $kw) {
                $keywordsArr[] = (string) $kw;
            }
        }
        $keywords = implode(', ', $keywordsArr);

        // Hitung Total Halaman
        $pages = '';
        if (isset($article->startPage) && isset($article->endPage)) {
            $start = (int) $article->startPage;
            $end = (int) $article->endPage;
            if ($end >= $start) {
                $pages = ($end - $start) + 1;
            }
        }

        // 🔥 Ekstrak Nama Jurnal dan Penerbit dari XML
        $journalTitle = isset($article->journalTitle) ? (string) $article->journalTitle : '';
        $publisher = isset($article->publisher) ? (string) $article->publisher : '';

        $extractedData = [
            'title' => $title,
            'journal_title' => $journalTitle, // Tambahkan ini
            'publisher' => $publisher,       // Tambahkan ini
            'abstract' => (string) $article->abstract,
            'doi' => $doi,
            'pub_year' => $pubYear,
            'authors' => $authorsList,
            'keywords' => $keywords,
            'pages' => $pages
        ];

        return view('submit_xml', compact('extractedData'));
    }

    // ==========================================
    // 3. Simpan Final ke Database (Setelah User Klik Save)
    // ==========================================
    public function storeXmlFinal(\Illuminate\Http\Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'title' => 'required|string',
            'abstract' => 'required|string',
            'document_type' => 'required|string',
            'submitter_first_name' => 'required|string',
            'submitter_last_name' => 'required|string',
            'submitter_email' => 'required|email',
            'authors' => 'required|array|min:1',
            'authors.*.name' => 'required|string',
            'authors.*.email' => 'required|email',
        ]);

        // ==========================================
        // 🔥 BENTENG FINAL: CEK DUPLIKASI JUDUL SEBELUM SIMPAN DB
        // ==========================================
        $existingDoc = \App\Models\Document::where('title', $request->title)->first();

        if ($existingDoc) {
            if (!$existingDoc->is_verified) {
                return redirect('/receipt/' . $existingDoc->document_number)
                    ->with('error', 'This document title has already been submitted and is awaiting email verification.');
            } else {
                return back()
                    ->withInput()
                    ->with('error', 'System Rejection: Document with title "' . $request->title . '" has already been officially indexed in our database.');
            }
        }

        // =======================================================
        // 🔥 FITUR BARU: AUTO-FORMAT DOI & FETCH SITASI CROSSREF
        // =======================================================
        $finalDoi = null;
        $citationCount = 0; // Default 0

        if (!empty($request->doi)) {
            $inputDoi = $request->doi;
            
            // 1. Pastikan jadi format link URL yang bisa diklik
            if (!str_starts_with($inputDoi, 'http')) {
                $finalDoi = 'https://doi.org/' . $inputDoi;
            } else {
                $finalDoi = $inputDoi;
            }

            // 2. Ekstrak kode mentah DOI (10.xxxx/yyyy) untuk nembak API Crossref
            if (preg_match('/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i', $finalDoi, $matches)) {
                $rawDoi = $matches[0];
                
                try {
                    // Tembak API Crossref dengan timeout 5 detik
                    $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://api.crossref.org/works/' . $rawDoi);
                    
                    if ($response->successful()) {
                        // Curi angka sitasinya!
                        $citationCount = $response->json('message.is-referenced-by-count') ?? 0;
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Gagal ambil sitasi Crossref XML untuk DOI: " . $rawDoi);
                }
            }
        }
        // =======================================================

        // 2. Simpan ke Tabel Documents
        $document = \App\Models\Document::create([
            'document_number' => 'OJS-' . strtoupper(uniqid()),
            'title' => $request->title,
            'journal_title' => $request->journal_title, // 🔥 SIMPAN NAMA JURNAL
            'publisher' => $request->publisher,         // 🔥 SIMPAN PENERBIT
            'abstract' => $request->abstract,
            'document_type' => $request->document_type, 
            'pub_year' => $request->pub_year,
            'doi' => $finalDoi, 
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

        // =========================================================
        // 🔥 FITUR BARU: LANGSUNG CATAT KE TABEL HISTORY SAAT SUBMIT
        // =========================================================
        \Illuminate\Support\Facades\DB::table('citation_histories')->insert([
            'document_id' => $document->id,
            'citation_count' => $citationCount, // Angka dari Crossref saat scan XML
            'year' => date('Y'),
            'month' => date('m'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Simpan Authors & Institusi (Pivot Table)
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
                }

                $author = \App\Models\Author::firstOrCreate(
                    ['email' => $authorData['email']], 
                    [
                        'name' => $authorData['name'],
                        'institution_id' => $institutionId,
                        'country' => $authorData['country'] ?? 'Unknown',
                    ]
                );

                $authorIdsToAttach[] = $author->id; 
            }

            $document->authors()->attach($authorIdsToAttach);
        }

        // Kirim Email Verifikasi
        dispatch(function () use ($document) {
            \Illuminate\Support\Facades\Mail::to($document->submitter_email)
                ->send(new \App\Mail\VerifyDocumentEmail($document));
        })->afterResponse();

        return redirect('/receipt/' . $document->document_number)->with('success', 'XML data saved! Please check your email to verify and activate the index.');
    }

    // =======================================================
    // 10. FITUR EDIT: Request & Kirim URL Terenkripsi
    // =======================================================
    public function requestEdit(Request $request, $id)
    {
        $request->validate(['email' => 'required|email']);
        
        $document = Document::where('document_number', $id)->firstOrFail();

        if (strtolower($document->submitter_email) !== strtolower($request->email)) {
            return response()->json(['error' => 'Authentication failed: Email does not match the original submitter of this document.'], 403);
        }

        // 🔥 TRIK DEWA: Sisipkan timestamp updated_at ke dalam link
        $secureEditUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'document.edit', 
            now()->addHours(24), 
            [
                'id' => $document->document_number,
                'v' => $document->updated_at->timestamp // Kunci gembok sekali pakai
            ]
        );

        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($document, $secureEditUrl) {
            $message->to($document->submitter_email)
                    ->subject('SustaIndex - Secure Document Edit Access')
                    ->html("
                        <h3>Secure Edit Request</h3>
                        <p>Hello,</p>
                        <p>You have requested to edit the metadata for the document: <br><b>{$document->title}</b></p>
                        <p>Please click the secure button below to access the edit form. For security reasons, this link is for <b>ONE-TIME USE ONLY</b> and will automatically expire once you save your changes (or within 24 hours).</p>
                        <br>
                        <a href='{$secureEditUrl}' style='background-color: #003366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Open Edit Form</a>
                        <br><br>
                        <p style='color: gray; font-size: 12px;'>If you did not request this, please ignore this email.</p>
                    ");
        });

        return response()->json(['success' => true]);
    }

    // =======================================================
    // 11. FITUR EDIT: Tampilkan Form Edit (One-Time Link Check)
    // =======================================================
    public function editForm(Request $request, $id)
    {
        $document = Document::with('authors')->where('document_number', $id)->firstOrFail();
        
        // 🔥 PENJAGA PINTU: Cek apakah timestamp di link masih sama dengan di Database?
        // Jika dokumen sudah pernah di-save/di-update, otomatis timestamp-nya berbeda!
        if ($request->query('v') != $document->updated_at->timestamp) {
            return abort(403, '🔒 SECURITY ALERT: This edit link has already been used and is now expired. If you need to make further corrections, please request a new secure link from the document page.');
        }

        return view('edit_document', compact('document'));
    }

    // =======================================================
    // 12. FITUR EDIT: Proses Update Super Lengkap
    // =======================================================
    public function updateDocument(Request $request, $id)
    {
        $document = Document::with('authors')->where('document_number', $id)->firstOrFail();

        $request->validate([
            'title' => 'required|string',
            'abstract' => 'required|string',
            'document_type' => 'required|string',
            'authors' => 'required|array|min:1',
            'authors.*.name' => 'required|string',
            'authors.*.email' => 'required|email',
            'authors.*.institution' => 'required|string',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Update Tabel Utama Dokumen
            $document->update([
                'title' => $request->title,
                'journal_title' => $request->journal_title,
                'publisher' => $request->publisher,
                'abstract' => $request->abstract,
                'keywords' => $request->keywords,
                'document_type' => $request->document_type,
                'pub_year' => $request->pub_year,
                'doi' => $request->doi,
                'pages' => $request->pages,
                'reference_count' => $request->reference_count,
            ]);

            // 2. Sinkronisasi Data Author & Institusi
            $authorIdsToSync = [];

            foreach ($request->authors as $authorData) {
                // Cek atau Buat Institusi Baru
                $institution = \App\Models\Institution::firstOrCreate(
                    ['name' => $authorData['institution']],
                    [
                        'country' => $authorData['country'] ?? null,
                        'latitude' => $authorData['lat'] ?? null,
                        'longitude' => $authorData['lng'] ?? null
                    ]
                );

                // Update koordinat kampus jika ada perubahan dari Leaflet Map
                if (isset($authorData['lat']) && isset($authorData['lng'])) {
                    $institution->update([
                        'latitude' => $authorData['lat'],
                        'longitude' => $authorData['lng']
                    ]);
                }

                // Cari Author berdasarkan email (untuk menghindari duplikasi)
                $author = \App\Models\Author::firstOrCreate(
                    ['email' => $authorData['email']], 
                    [
                        'name' => $authorData['name'],
                        'country' => $authorData['country'] ?? null,
                        'institution_id' => $institution->id
                    ]
                );

                // Update jika ada perubahan typo pada nama/negara
                $author->update([
                    'name' => $authorData['name'],
                    'country' => $authorData['country'] ?? null,
                    'institution_id' => $institution->id
                ]);

                $authorIdsToSync[] = $author->id;
            }

            // Sync Pivot Table: Ini otomatis menghapus author yang di-remove, dan memasukkan yang di-add
            $document->authors()->sync($authorIdsToSync);

            \Illuminate\Support\Facades\DB::commit();

            // 3. Kirim Email Notifikasi
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($document) {
                $message->to($document->submitter_email)
                        ->subject('SustaIndex - Document Updated Successfully')
                        ->html("
                            <h3>Update Successful</h3>
                            <p>Hello,</p>
                            <p>The metadata for your document <b>{$document->title}</b> has been successfully updated in our index.</p>
                            <p>Thank you for keeping your academic records accurate!</p>
                        ");
            });

            return redirect('/document/' . $document->document_number)->with('success', 'Document information has been successfully updated!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
}