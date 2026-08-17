<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $docTypes = Document::where('is_verified', true)
                        ->select('document_type', DB::raw('count(*) as total'))
                        ->groupBy('document_type')
                        ->get();

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

        $mostCited = Document::where('is_verified', true)->orderBy('citation_count', 'desc')->take(3)->get();
        $mostPopular = Document::where('is_verified', true)->orderBy('views', 'desc')->take(3)->get();

        return view('index', compact('docTypes', 'yearStats', 'mostCited', 'mostPopular'));
    }
}