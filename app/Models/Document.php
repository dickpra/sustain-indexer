<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // <-- Jangan lupa tambahkan ini

class Document extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    // 🔥 FITUR BARU: Mendaftarkan atribut siluman agar muncul di JSON (index.blade)
    protected $appends = ['real_citation_count'];

    // Relasi: 1 Dokumen Jurnal ditulis oleh banyak Author
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_document');
    }

    // =======================================================
    // 🔥 ACCESSOR: AMBIL SITASI LANGSUNG DARI TABEL HISTORY
    // =======================================================
    public function getRealCitationCountAttribute()
    {
        // Tarik data paling baru dari tabel citation_histories
        $latestHistory = DB::table('citation_histories')
                            ->where('document_id', $this->id)
                            ->orderBy('created_at', 'desc')
                            ->first();
                            
        // Kalau ada di history, pakai angka itu. Kalau tabelnya masih kosong (belum disync), pakai angka bawaan form.
        return $latestHistory ? $latestHistory->citation_count : $this->citation_count;
    }
}