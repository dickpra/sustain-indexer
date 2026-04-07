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


class DocumentController extends Controller
{
    // Fungsi menerima data dari form
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar (Perhatikan 'authors' sekarang wajib array)
        $validated = $request->validate([
            'title' => 'required|string',
            'authors' => 'required|array|min:1', // Harus berupa array, minimal 1 author
            'authors.*' => 'required|string', // Isi dari tiap author harus string
            'abstract' => 'required|string',
            'pdf_file' => 'required|mimes:pdf|max:102400',
            'submitter_email' => 'required|email',
            'submitter_first_name' => 'required|string',
            'submitter_last_name' => 'required|string',
        ]);

        // 2. Simpan & Ekstrak PDF
        // 2. Simpan & Ekstrak PDF
        $path = $request->file('pdf_file')->store('temp');
        $fullPath = storage_path('app/' . $path);
        
        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);
        $rawText = $pdf->getText();

        // 3. PEMBERSIHAN EKSTREM
        // Hanya menyisakan huruf kecil dan angka (hapus semua spasi, enter, dan tanda baca)
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
            // Bersihkan nama author secara ekstrem juga
            $ultraCleanAuthor = preg_replace('/[^a-z0-9]/', '', strtolower($author));
            
            if (!str_contains($ultraCleanPdfText, $ultraCleanAuthor)) {
                Storage::delete($path);
                return response()->json([
                    'error' => 'Validation Rejected: Author name ("' . $author . '") not found in the PDF file.'
                ], 422);
            }
        }

        // 6. Ekstraksi DOI (Pakai $rawText karena DOI butuh tanda baca / dan .)
        $doi = null;
        $doiPattern = '/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i';
        if (preg_match($doiPattern, $rawText, $matches)) {
            $doi = 'https://doi.org/' . $matches[0];
        }

        // 6. Generate ID & Simpan Data
        $docNumber = 'IDX-' . rand(100000, 999999);
        $token = Str::random(40);

        $documentData = array_merge($validated, [
            'document_number' => $docNumber,
            'authors' => $request->authors, // Laravel otomatis mengubah array ini jadi JSON karena model $casts
            'doi' => $doi,
            'verification_token' => $token,
            'document_type' => $request->document_type,
            'pub_year' => $request->pub_year,
            'pages' => $request->pages,
            'reference_count' => $request->reference_count,
        ]);
        
        $document = Document::create($documentData);

        // 7. Kirim Email & Hapus File
        Mail::to($request->submitter_email)->send(new VerifyDocumentEmail($document));
        Storage::delete($path);

        return response()->json([
            'status' => 'success',
            'confirmation_id' => $docNumber
        ]);
    }

    // Fungsi Baru: Saat user klik link di email
    public function verifyEmail($token)
    {
        // Cari dokumen yang tokennya cocok
        $document = Document::where('verification_token', $token)->first();

        if (!$document) {
            return "Link verifikasi tidak valid atau sudah kedaluwarsa.";
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
                        ->select('document_type', \DB::raw('count(*) as total'))
                        ->groupBy('document_type')
                        ->get();

        // Hitung jumlah dokumen per tahun publikasi
        $pubYears = Document::where('is_verified', true)
                        ->select('pub_year', \DB::raw('count(*) as total'))
                        ->groupBy('pub_year')
                        ->orderBy('pub_year', 'desc')
                        ->get();

        return view('index', compact('docTypes', 'pubYears'));
    }

    // 2. Fungsi Pencarian API (Update)
    public function search(Request $request)
    {
        $q = $request->query('q');
        
        // Hanya cari dokumen yang is_verified = true
        $query = Document::where('is_verified', true);

        if ($q) {
            $query->where(function($queryBuilder) use ($q) {
                $queryBuilder->where('title', 'like', "%$q%")
                             ->orWhere('abstract', 'like', "%$q%")
                             ->orWhere('authors', 'like', "%$q%");
            });
        }

        // Tampilkan yang terbaru
        $results = $query->latest()->get();
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
}