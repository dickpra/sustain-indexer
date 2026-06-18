<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class Author extends Model
{
    protected $guarded = [];

    // Relasi: 1 Author bisa punya banyak Dokumen Jurnal
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'author_document');
    }

    // Relasi: 1 Author berada di 1 Institusi
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function getSFactorAttribute()
    {
        // Ambil data 3 tahun terakhir
        $threeYearsAgo = Carbon::now()->subYears(3)->year;
        
        $recentDocs = $this->documents()->where('pub_year', '>=', $threeYearsAgo)->get();
        
        $totalPublished = $recentDocs->count();
        if ($totalPublished == 0) return 0; // Hindari error pembagian dengan nol

        $totalCitations = $recentDocs->sum('citation_count');
        $qModifier = 0.1; // Sesuai dokumen kamu

        $sFactor = ($totalCitations / $totalPublished) * $qModifier;

        return number_format($sFactor, 2); // Bulatkan 2 angka di belakang koma
    }

    // ==========================================
    // 2. CALCULATE S-INDEX
    // ==========================================
    public function getSIndexAttribute()
    {
        // Parameter h (Total Sitasi)
        $h = $this->documents()->sum('citation_count');

        // Parameter I (International Collaboration Score)
        // Logika sementara: Hitung jumlah negara unik dari institusi rekan penulis
        $uniqueCountries = $this->documents()
            ->with('authors')
            ->get()
            ->pluck('authors')
            ->flatten()
            ->pluck('country')
            ->filter()
            ->unique()
            ->count();
        
        // Asumsi sederhana untuk I: (1 institusi utama * 0.1) + (Jumlah Negara * 0.2)
        $i = 0.1 + ($uniqueCountries * 0.2); 

        // Parameter G (SDGs Score)
        // Logika sederhana: Hitung berapa dokumen yang punya keyword "SDG" atau "sustainability"
        $g = $this->documents()
            ->where(function($query) {
                $query->where('keywords', 'LIKE', '%sustainabil%')
                      ->orWhere('keywords', 'LIKE', '%SDG%');
            })->count();

        // Parameter C (???) -> Nanti kita bahas di bawah
        $c = 0; 

        // Eksekusi Rumus S-Index
        $sIndex = ($h * 0.01) + ($i * 0.1) + ($c * 0.2) + ($g * 0.1);

        return number_format($sIndex, 2);
    }
}