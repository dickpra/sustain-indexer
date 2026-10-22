<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Author;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Data Tipe Dokumen
        $docTypes = Document::where('is_verified', true)
                        ->select('document_type', DB::raw('count(*) as total'))
                        ->groupBy('document_type')
                        ->get();

        // 2. Data Top 5 Publisher untuk Filter
        $topPublishers = Document::where('is_verified', true)
                        ->whereNotNull('publisher')
                        ->where('publisher', '!=', '')
                        ->select('publisher', DB::raw('count(*) as total'))
                        ->groupBy('publisher')
                        ->orderBy('total', 'desc')
                        ->take(5)
                        ->get();

        // 3. Data Statistik Tahun
        $currentYear = date('Y');
        $yearStats = [
            'current_year' => $currentYear,
            'count_current' => Document::where('is_verified', true)->where('pub_year', $currentYear)->count(),
            'last_year' => $currentYear - 1,
            'count_last' => Document::where('is_verified', true)->where('pub_year', '>=', $currentYear - 1)->count(),
            'year_5' => $currentYear - 4,
            'count_5' => Document::where('is_verified', true)->where('pub_year', '>=', $currentYear - 4)->count(),
            'year_10' => $currentYear - 9,
            'count_10' => Document::where('is_verified', true)->where('pub_year', '>=', $currentYear - 9)->count(),
            'year_20' => $currentYear - 19,
            'count_20' => Document::where('is_verified', true)->where('pub_year', '>=', $currentYear - 19)->count(),
        ];

        // 4. Data Trending / Leaderboards
        $mostCited = Document::where('is_verified', true)->orderBy('citation_count', 'desc')->take(3)->get();
        $mostPopular = Document::where('is_verified', true)->orderBy('views', 'desc')->take(3)->get();

        $totalVisitors = \App\Models\Document::sum('views');

        return view('index', compact('docTypes', 'topPublishers', 'yearStats', 'mostCited', 'mostPopular', 'totalVisitors'));
    }

    // ==========================================
    // 🔥 HALAMAN SUSTAINDEX RANKINGS (ANTI BUG SERI)
    // ==========================================
    public function rankings()
    {
        // 1. Most Productive
        $topResearchers = \App\Models\Author::withCount('documents')
                            ->with('institution')
                            ->orderByDesc('documents_count')
                            ->orderBy('name', 'asc') // Tie-breaker abjad
                            ->take(5)->get();

        // 2. Top Sustainable
        $topSustainable = \App\Models\Author::with('institution')
            ->addSelect(['sdg_count' => \Illuminate\Support\Facades\DB::table('document_sdgs')
                ->join('author_document', 'document_sdgs.document_id', '=', 'author_document.document_id')
                ->whereColumn('author_document.author_id', 'authors.id')
                ->selectRaw('count(*)')
            ])
            ->orderByDesc('sdg_count')
            ->orderBy('authors.name', 'asc') // 🔥 FIX BUG: Tie-breaker abjad
            ->take(5)->get();

        // 3. Most Impactful Researchers
        $mostImpactful = \App\Models\Author::with('institution')
            ->addSelect(['total_citations' => \Illuminate\Support\Facades\DB::table('documents')
                ->join('author_document', 'documents.id', '=', 'author_document.document_id')
                ->whereColumn('author_document.author_id', 'authors.id')
                ->selectRaw('sum(citation_count)')
            ])
            ->orderByDesc('total_citations')
            ->orderBy('authors.name', 'asc') // Tie-breaker abjad
            ->take(5)->get();

        // 4. Interdisciplinary Leaders
        $interdisciplinary = \App\Models\Author::with('institution')
            ->addSelect(['distinct_sdgs' => \Illuminate\Support\Facades\DB::table('document_sdgs')
                ->join('author_document', 'document_sdgs.document_id', '=', 'author_document.document_id')
                ->whereColumn('author_document.author_id', 'authors.id')
                ->selectRaw('count(distinct document_sdgs.sdg_code)')
            ])
            ->orderByDesc('distinct_sdgs')
            ->orderBy('authors.name', 'asc')
            ->take(5)->get();

        // 5. Rising Stars
        $risingStars = \App\Models\Author::with('institution')
            ->addSelect(['recent_docs' => \Illuminate\Support\Facades\DB::table('documents')
                ->join('author_document', 'documents.id', '=', 'author_document.document_id')
                ->whereColumn('author_document.author_id', 'authors.id')
                ->where('pub_year', '>=', date('Y') - 2)
                ->selectRaw('count(*)')
            ])
            ->orderByDesc('recent_docs')
            ->orderBy('authors.name', 'asc')
            ->take(5)->get();

        // 6. Most Collaborative
        $collaborative = \App\Models\Author::with('institution')
            ->addSelect(['collab_count' => \Illuminate\Support\Facades\DB::table('author_document as ad1')
                ->join('author_document as ad2', 'ad1.document_id', '=', 'ad2.document_id')
                ->whereColumn('ad1.author_id', 'authors.id')
                ->whereColumn('ad2.author_id', '!=', 'authors.id')
                ->selectRaw('count(distinct ad2.author_id)')
            ])
            ->orderByDesc('collab_count')
            ->orderBy('authors.name', 'asc')
            ->take(5)->get();

        // 7. Top Institutions
        $topInstitutions = \App\Models\Institution::withCount('authors')
                            ->orderByDesc('authors_count')
                            ->orderBy('name', 'asc')
                            ->take(5)->get();

        // 8. Top Countries
        $topCountries = \App\Models\Institution::select('country', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                            ->whereNotNull('country')
                            ->where('country', '!=', '')
                            ->groupBy('country')
                            ->orderByDesc('total')
                            ->orderBy('country', 'asc')
                            ->take(5)->get();

        // 9. Most Impactful Articles
        $mostCited = \App\Models\Document::where('is_verified', true)
                            ->orderByDesc('citation_count')
                            ->orderBy('title', 'asc')
                            ->take(5)->get();

        return view('rankings', compact(
            'topResearchers', 'topSustainable', 'mostImpactful', 
            'interdisciplinary', 'risingStars', 'collaborative', 
            'topInstitutions', 'topCountries', 'mostCited'
        ));
    }
}