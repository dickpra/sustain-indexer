<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Tambahkan ini untuk bikin random string
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyDocumentEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    // Fungsi menerima data dari form
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'title' => 'required|string',
            'authors' => 'required|array|min:1', 
            'authors.*' => 'required|string', 
            'abstract' => 'required|string',
            'pdf_file' => 'required|mimes:pdf|max:102400',
            'submitter_email' => 'required|email',
            'submitter_first_name' => 'required|string',
            'submitter_last_name' => 'required|string',
        ]);

        // 2. Simpan & Ekstrak PDF
        $path = $request->file('pdf_file')->store('temp');
        $fullPath = storage_path('app/' . $path);
        
        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);
        $rawText = $pdf->getText();

        // 3. PEMBERSIHAN EKSTREM
        $ultraCleanPdfText = preg_replace('/[^a-z0-9]/', '', strtolower($rawText));
        $ultraCleanInputTitle = preg_replace('/[^a-z0-9]/', '', strtolower($request->title));

        // 4. DUAL VALIDATION: Cek Judul
        if (!str_contains($ultraCleanPdfText, $ultraCleanInputTitle)) {
            Storage::delete($path);
            return response()->json([
                'error' => 'Validation Rejected: Title of the document not found in the PDF file. Please make sure the title is spelled correctly.'
            ], 422);
        }

        // 5. DUAL VALIDATION: Cek SEMUA Author
        foreach ($request->authors as $author) {
            $ultraCleanAuthor = preg_replace('/[^a-z0-9]/', '', strtolower($author));
            
            if (!str_contains($ultraCleanPdfText, $ultraCleanAuthor)) {
                Storage::delete($path);
                return response()->json([
                    'error' => 'Validation Rejected: Author name ("' . $author . '") not found in the PDF file.'
                ], 422);
            }
        }

        // 6. Ekstraksi DOI
        $doi = null;
        $doiPattern = '/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i';
        if (preg_match($doiPattern, $rawText, $matches)) {
            $doi = 'https://doi.org/' . $matches[0];
        }

        // 7. Siapkan Data
        $docNumber = 'IDX-' . rand(100000, 999999);
        $token = \Illuminate\Support\Str::random(40);

        $documentData = array_merge($validated, [
            'document_number' => $docNumber,
            'authors' => $request->authors, 
            'doi' => $doi,
            'verification_token' => $token,
            'document_type' => $request->document_type,
            'pub_year' => $request->pub_year,
            'pages' => $request->pages,
            'reference_count' => $request->reference_count,
        ]);

        // =========================================================
        // 8. TRANSAKSI DATABASE (ANTI-GAGAL)
        // =========================================================
        DB::beginTransaction();

        try {
            // A. Coba simpan ke Database
            $document = Document::create($documentData);

            // B. Coba kirim Email
            // CARA RAHASIA: AFTER RESPONSE (Tanpa Worker, Tapi Layar Cepat)
            dispatch(function () use ($document, $request) {
                \Illuminate\Support\Facades\Mail::to($request->submitter_email)
                    ->send(new \App\Mail\VerifyDocumentEmail($document));
            })->afterResponse();

            // C. Jika A dan B sukses, resmikan data masuk ke Database!
            DB::commit();

            // D. Buang sampah PDF
            Storage::delete($path);

            return response()->json([
                'status' => 'success',
                'confirmation_id' => $docNumber
            ], 200);

        } catch (\Exception $e) {
            // JIKA ADA ERROR (Misal SMTP mati, internet putus, dll)
            
            // 1. Batalkan/Tarik kembali data dari database!
            DB::rollBack();

            // 2. Tetap buang sampah PDF supaya hosting tidak penuh
            Storage::delete($path);

            // 3. Catat error aslinya di file log supaya programmer tahu
            Log::error('SustainDex Submit Error: ' . $e->getMessage());

            // 4. Kasih tahu user kalau sistem sedang gangguan
            return response()->json([
                'error' => 'Submission failed due to a system error. Please try again later or contact support if the issue persists.'
            ], 500);
        }
    }

    // Fungsi Baru: Saat user klik link di email
    public function verifyEmail($token)
    {
        // Cari dokumen yang tokennya cocok
        $document = Document::where('verification_token', $token)->first();

        if (!$document) {
            return "Verification link is invalid or has expired.";
        }

        // Kalau ketemu, ubah jadi verified dan hapus tokennya biar gak dipakai 2 kali
        $document->update([
            'is_verified' => true,
            'verification_token' => null
        ]);

        return "Congratulations! Your document has been successfully verified and now it is available in the search system.";
    }

    // 1. Fungsi untuk menampilkan Halaman Utama (index.blade.php)
    public function index()
    {
        // Hitung jumlah dokumen per tipe (hanya yang sudah verified)
        $docTypes = Document::where('is_verified', true)
                        ->select('document_type', DB::raw('count(*) as total'))
                        ->groupBy('document_type')
                        ->get();

        // Hitung jumlah dokumen per tahun publikasi
        $pubYears = Document::where('is_verified', true)
                        ->select('pub_year', DB::raw('count(*) as total'))
                        ->groupBy('pub_year')
                        ->orderBy('pub_year', 'desc')
                        ->get();

        return view('index', compact('docTypes', 'pubYears'));
    }

    // 2. Fungsi Pencarian API (Update)
    // Fungsi Pencarian API (Update dengan Filter)
    public function search(Request $request)
    {
        $q = $request->query('q');
        $type = $request->query('type'); // Menangkap klik Tipe dari UI
        $year = $request->query('year'); // Menangkap klik Tahun dari UI
        
        // Hanya cari dokumen yang is_verified = true
        $query = Document::where('is_verified', true);

        // Jika user mengetik di kotak pencarian
        if ($q) {
            $query->where(function($queryBuilder) use ($q) {
                $queryBuilder->where('title', 'like', "%$q%")
                             ->orWhere('abstract', 'like', "%$q%")
                             ->orWhere('authors', 'like', "%$q%")
                             ->orWhere('document_number', 'like', "%$q%"); // Bisa cari pakai ID
            });
        }

        // Jika user mengklik filter Tipe Dokumen di kiri
        if ($type) {
            $query->where('document_type', $type);
        }

        // Jika user mengklik filter Tahun Publikasi di kiri
        if ($year) {
            $query->where('pub_year', $year);
        }

        // Tampilkan yang terbaru
        // CARA BARU (Membatasi 10 data per halaman)
        $results = $query->latest()->paginate(10);
        return response()->json($results);
    }

    // Fungsi Detail untuk Halaman Detail
    public function show($document_number)
    {
        // Cari dokumen berdasarkan ID, dan pastikan sudah di-verify
        $document = Document::where('document_number', $document_number)
                            ->where('is_verified', true)
                            ->firstOrFail();

        // Decode JSON authors kalau masih berbentuk string
        $authors = is_array($document->authors) ? $document->authors : json_decode($document->authors, true);

        return view('show', compact('document', 'authors'));
    }

    // Fungsi Menampilkan Halaman Receipt
    public function receipt($id)
    {
        // Cari dokumen berdasarkan ID. Jika tidak ada, otomatis muncul error 404
        $document = Document::where('document_number', $id)->firstOrFail();
        
        return view('receipt', compact('document'));
    }

    // Fungsi Mengirim Ulang Email
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

        // Masukkan ulang tugas kirim email ke dalam antrean (Queue)
        dispatch(function () use ($document) {
            \Illuminate\Support\Facades\Mail::to($document->submitter_email)
                ->send(new \App\Mail\VerifyDocumentEmail($document));
        })->afterResponse();

        return response()->json(['message' => 'Verification email has been resent successfully! Please check your inbox or spam folder.']);
    }
}