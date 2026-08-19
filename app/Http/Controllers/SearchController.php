<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            // 1. Ambil Parameter dari URL
            $q = $request->query('q');
            $type = $request->query('type');
            $year = $request->query('year');
            $authorFilter = $request->query('author');
            $publisherFilter = $request->query('publisher');
            $sdgFilter = $request->query('sdg'); 
            
            $baseQuery = Document::with('authors.institution')->where('is_verified', true);

            // ==========================================
            // 🔥 FEATURED PROFILES (SMART MULTI-MATCH MAX 3)
            // ==========================================
            $featuredInstitutions = collect();
            $featuredAuthors = collect();

            if (!empty($q) && strlen(trim($q)) >= 3) {
                // 1. Pecah kata kunci per spasi (Biar bisa cari "Dwi Wulan" -> "Dwi Sri Wulandari")
                $keywords = explode(' ', trim($q));

                // 2. Cari Institusi (Makin banyak authornya, makin atas posisinya)
                $instQuery = \App\Models\Institution::withCount('authors');
                foreach ($keywords as $word) {
                    $instQuery->where('name', 'like', "%{$word}%");
                }
                $featuredInstitutions = $instQuery->orderBy('authors_count', 'desc')->take(3)->get();

                // 3. Cari Peneliti (Makin banyak jurnalnya, makin atas posisinya)
                $authQuery = \App\Models\Author::with('institution')->withCount('documents');
                foreach ($keywords as $word) {
                    $authQuery->where('name', 'like', "%{$word}%");
                }
                $featuredAuthors = $authQuery->orderBy('documents_count', 'desc')->take(3)->get();
            }

            // Filter Pencarian Utama Dokumen
            if ($q) {
                $baseQuery->where(function($queryBuilder) use ($q) {
                    $queryBuilder->where('title', 'like', "%$q%")
                                 ->orWhere('abstract', 'like', "%$q%")
                                 ->orWhere('keywords', 'like', "%$q%")
                                 ->orWhere('document_number', 'like', "%$q%")
                                 ->orWhere('publisher', 'like', "%$q%")
                                 ->orWhere('journal_title', 'like', "%$q%")
                                 ->orWhereHas('authors', function($authorQuery) use ($q) {
                                     $authorQuery->where('name', 'like', "%$q%");
                                 })
                                 ->orWhereHas('authors.institution', function($instQuery) use ($q) {
                                     $instQuery->where('name', 'like', "%$q%");
                                 });
                });
            }

            if ($authorFilter) {
                $baseQuery->whereHas('authors', function($authorQuery) use ($authorFilter) {
                    $authorQuery->where('name', 'like', "%{$authorFilter}%");
                });
            }

            // 🔥 PERBAIKAN: Gunakan (clone $baseQuery) agar opsinya menyusut sesuai kata kunci pencarian!
            $docTypes = (clone $baseQuery)
                            ->select('document_type', DB::raw('count(*) as total'))
                            ->groupBy('document_type')
                            ->get();

            $topPublishers = (clone $baseQuery)
                            ->whereNotNull('publisher')
                            ->where('publisher', '!=', '')
                            ->select('publisher', DB::raw('count(*) as total'))
                            ->groupBy('publisher')
                            ->orderBy('total', 'desc')
                            ->take(5)
                            ->get();

            $currentYear = date('Y');
            $yearStats = [
                'current_year' => $currentYear,
                'count_current' => (clone $baseQuery)->where('pub_year', $currentYear)->count(),
                'last_year' => $currentYear - 1,
                'count_last' => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 1)->count(),
                'year_5' => $currentYear - 4,
                'count_5' => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 4)->count(),
                'year_10' => $currentYear - 9,
                'count_10' => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 9)->count(),
                'year_20' => $currentYear - 19,
                'count_20' => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 19)->count(),
            ];

            $sdgDictionary = [
                "SDG 1" => "No Poverty", "SDG 2" => "Zero Hunger", "SDG 3" => "Good Health & Well-being",
                "SDG 4" => "Quality Education", "SDG 5" => "Gender Equality", "SDG 6" => "Clean Water & Sanitation",
                "SDG 7" => "Affordable & Clean Energy", "SDG 8" => "Decent Work & Economy",
                "SDG 9" => "Industry & Innovation", "SDG 10" => "Reduced Inequality",
                "SDG 11" => "Sustainable Cities", "SDG 12" => "Responsible Consumption",
                "SDG 13" => "Climate Action", "SDG 14" => "Life Below Water", "SDG 15" => "Life on Land",
                "SDG 16" => "Peace & Justice", "SDG 17" => "Partnerships for Goals"
            ];

            $sdgFacets = [];
            foreach ($sdgDictionary as $sdgKey => $sdgName) {
                $countSdg = (clone $baseQuery)->where('keywords', 'like', "%{$sdgKey}%")->count();
                if ($countSdg > 0) {
                    $sdgFacets[$sdgKey] = ['name' => $sdgName, 'count' => $countSdg];
                }
            }

            $mostCited = Document::where('is_verified', true)->orderBy('citation_count', 'desc')->take(3)->get();
            $mostPopular = Document::where('is_verified', true)->orderBy('views', 'desc')->take(3)->get();

            // Eksekusi Filter aktif
            $query = clone $baseQuery;
            if ($type) $query->where('document_type', $type);
            if ($publisherFilter) $query->where('publisher', $publisherFilter);
            if ($sdgFilter) $query->where('keywords', 'like', "%{$sdgFilter}%"); 
            
            if ($year) {
                if (\Illuminate\Support\Str::startsWith($year, 'exact_')) {
                    $query->where('pub_year', str_replace('exact_', '', $year));
                } elseif (\Illuminate\Support\Str::startsWith($year, 'since_')) {
                    $query->where('pub_year', '>=', str_replace('since_', '', $year));
                }
            }

            $results = $query->orderBy('pub_year', 'desc')->latest()->paginate(10);
            $results->appends($request->query()); 

            return view('index', compact(
                'results', 'docTypes', 'topPublishers', 'yearStats', 'sdgFacets', 'mostCited', 'mostPopular',
                'featuredInstitutions', 'featuredAuthors'
            ));

        } catch (\Throwable $e) { 
            \Illuminate\Support\Facades\Log::error("SustaIndex Search Error: " . $e->getMessage());
            return back()->with('error', 'Search system error: ' . $e->getMessage());
        }
    }
}