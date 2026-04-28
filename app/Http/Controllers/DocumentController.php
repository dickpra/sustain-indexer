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
        $docTypes = Document::where('is_verified', true)
                        ->select('document_type', DB::raw('count(*) as total'))
                        ->groupBy('document_type')
                        ->get();

        // ==========================================
        // FITUR BARU: STATISTIK TAHUN ALA GOOGLE SCHOLAR
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

        // Jangan lupa kirim variabel $yearStats ke view!
        return view('index', compact('docTypes', 'yearStats'));
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

        // 2. PAKSA tarik relasi author + kampusnya menggunakan METHOD (Pasti dapet Collection, gak mungkin null)
        $authors = $document->authors()->with('institution')->get();

        // 3. Lempar dua-duanya ke view secara terpisah
        return view('show', compact('document', 'authors'));
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

        // Kalau yang minta adalah Browser (Loading awal)
        return view('author_profile', compact('author'));
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
}