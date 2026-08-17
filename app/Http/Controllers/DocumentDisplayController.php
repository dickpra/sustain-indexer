<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\DB;

class DocumentDisplayController extends Controller
{
    public function show($id)
    {
        $document = Document::where('document_number', $id)->firstOrFail();
        $document->increment('views');

        $authors = $document->authors()->with('institution')->get();

        $latestCitation = DB::table('citation_histories')
                            ->where('document_id', $document->id)
                            ->latest('created_at')
                            ->first();

        return view('show', compact('document', 'authors', 'latestCitation'));
    }

    public function receipt($id)
    {
        $document = Document::where('document_number', $id)->firstOrFail();
        return view('receipt', compact('document'));
    }
}