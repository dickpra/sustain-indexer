<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Author;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;

class AuthorInstitutionController extends Controller
{
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

    public function searchInstitutions(Request $request)
    {
        $search = $request->query('q');
        if (!$search) return response()->json([]);

        $institutions = Institution::where('name', 'like', "%{$search}%")
                        ->limit(10)
                        ->get(['id', 'name', 'latitude', 'longitude']);
        
        return response()->json($institutions);
    }
}