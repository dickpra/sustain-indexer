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
    // =======================================================
    // 9. Fungsi Halaman Profil Jurnal/Publisher (S-FACTOR ADDED)
    // =======================================================
    public function showJournal($name)
    {
        $journalName = urldecode($name);

        // 🔥 Tarik nama publisher dari salah satu dokumen di jurnal ini
        $publisherName = \App\Models\Document::where('journal_title', $journalName)->value('publisher');

        $documents = \App\Models\Document::with('authors') 
                        ->where('journal_title', $journalName)
                        ->where('is_verified', true)
                        ->latest()
                        ->paginate(15);

        // Hitung total murni dari database
        $totalDocs = \App\Models\Document::where('journal_title', $journalName)->where('is_verified', true)->count();
        $totalCitations = \App\Models\Document::where('journal_title', $journalName)->where('is_verified', true)->sum('citation_count');
        $totalViews = \App\Models\Document::where('journal_title', $journalName)->where('is_verified', true)->sum('views');

        // 🔥 MAGIC: Hitung S-Factor secara dinamis (Tanpa buat kolom DB baru!)
        // Rumus: Total Sitasi dibagi Total Artikel (Dibulatkan 2 angka di belakang koma)
        $sFactor = $totalDocs > 0 ? number_format($totalCitations / $totalDocs, 2) : '0.00';

        return view('journal_profile', compact('journalName', 'publisherName', 'documents', 'totalCitations', 'totalViews', 'sFactor', 'totalDocs'));    }
}