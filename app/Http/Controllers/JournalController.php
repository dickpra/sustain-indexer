<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    // =======================================================
    // FUNGSI UNTUK MENAMPILKAN HALAMAN PROFIL JURNAL
    // =======================================================
    // =======================================================
    public function showJournal($name)
    {
        $journalName = urldecode($name);

        // 🔥 KODE WITH AUTHORS INI YANG MENGHILANGKAN "UNKNOWN AUTHOR"
        $documents = \App\Models\Document::with('authors') 
                        ->where('journal_title', $journalName)
                        ->where('is_verified', true)
                        ->latest()
                        ->paginate(15);

        $totalCitations = \App\Models\Document::where('journal_title', $journalName)->sum('citation_count');
        $totalViews = \App\Models\Document::where('journal_title', $journalName)->sum('views');

        return view('journal_profile', compact('journalName', 'documents', 'totalCitations', 'totalViews'));
    }
}