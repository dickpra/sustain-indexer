<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q');
        $type = $request->query('type');
        $year = $request->query('year');
        $authorFilter = $request->query('author');
        
        $baseQuery = Document::with('authors.institution')->where('is_verified', true);

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

        $typeFacets = (clone $baseQuery)
            ->select('document_type', DB::raw('count(*) as total'))
            ->groupBy('document_type')
            ->pluck('total', 'document_type');

        $currentYear = date('Y');
        $yearFacets = [
            'count_current' => (clone $baseQuery)->where('pub_year', $currentYear)->count(),
            'count_last'    => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 1)->count(),
            'count_5'       => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 4)->count(),
            'count_10'      => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 9)->count(),
            'count_20'      => (clone $baseQuery)->where('pub_year', '>=', $currentYear - 19)->count(),
        ];

        $query = clone $baseQuery;
        
        if ($type) $query->where('document_type', $type);

        if ($year) {
            if (str_starts_with($year, 'exact_')) {
                $query->where('pub_year', str_replace('exact_', '', $year));
            } elseif (str_starts_with($year, 'since_')) {
                $query->where('pub_year', '>=', str_replace('since_', '', $year));
            }
        }

        $results = $query->orderBy('pub_year', 'desc')->latest()->paginate(10);

        $response = $results->toArray();
        $response['facets'] = [
            'types' => $typeFacets,
            'years' => $yearFacets
        ];

        return response()->json($response);
    }
}