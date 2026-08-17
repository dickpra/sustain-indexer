<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Author;
use App\Models\Institution;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Mail\VerifyDocumentEmail;

class PdfSubmissionController extends Controller
{
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
            'sdgs' => 'required|array|min:1',
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
            'journal_title' => 'required|string', // <-- Sekarang Wajib
            'publisher' => 'required|string',     // <-- Sekarang Wajib
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

            // 🔥 Kita tembak API Crossref diem-diem buat validasi & nyuri data sitasi!
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://api.crossref.org/works/' . $rawDoi);
                
                if ($response->successful()) {
                    // Ambil angka sitasi dari JSON balasan Crossref
                    $citationCount = $response->json('message.is-referenced-by-count') ?? 0;
                } 
                // 🚨 SATPAM 1: TOLAK KALAU CROSSREF BILANG DOI PALSU/TIDAK ADA (404)
                elseif ($response->status() === 404) {
                    \Illuminate\Support\Facades\Storage::delete($path); // Hapus file temp
                    return response()->json([
                        'error' => 'System Rejection: The DOI (' . $rawDoi . ') found in the PDF is NOT registered in the Crossref database. Submission denied.'
                    ], 422);
                }
            } catch (\Exception $e) {
                // Kalau API Crossref lagi down, diamkan saja (tetap 0). Jangan ganggu proses submit.
                \Illuminate\Support\Facades\Log::warning("Gagal ambil sitasi Crossref untuk DOI: " . $rawDoi);
            }
        } else {
            // 🚨 SATPAM 2: TOLAK KALAU MESIN TIDAK MENEMUKAN TULISAN DOI DI DALAM PDF
            \Illuminate\Support\Facades\Storage::delete($path); // Hapus file temp
            return response()->json([
                'error' => 'System Rejection: No valid DOI (Digital Object Identifier) was found inside the uploaded PDF document. All submissions must include a registered DOI.'
            ], 422);
        }
        // =======================================================
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

            // 2. 🔥 MAGIC PENGGABUNGAN KEWORD & SDGs
            $originalKeywords = trim($request->input('keywords', ''));
            $sdgsString = implode(', ', $request->input('sdgs')); // Gabungkan array SDG jadi 1 kalimat
            
            // Kalau keyword aslinya kosong, pakai SDG saja. Kalau ada, gabungkan pakai koma.
            $finalKeywords = empty($originalKeywords) ? $sdgsString : $originalKeywords . ', ' . $sdgsString;

            $document = Document::create([
                'document_number' => $docNumber,
                'title' => $request->title,
                'journal_title' => $request->journal_title, // <-- Tambahan baru
                'publisher' => $request->publisher,
                'abstract' => $request->abstract,
                'keywords' => $finalKeywords,
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
}