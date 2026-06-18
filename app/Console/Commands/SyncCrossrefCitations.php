<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncCrossrefCitations extends Command
{
    // Nama perintah untuk dijalankan di terminal/cron
    protected $signature = 'sustaindex:sync-citations';
    protected $description = 'Weekly sync of document citations from Crossref API';

    public function handle()
    {
        $this->info('Memulai sinkronisasi sitasi...');

        // Cari semua dokumen yang punya DOI dan formatnya valid
        $documents = Document::whereNotNull('doi')->where('doi', 'LIKE', '%10.%')->get();
        $now = Carbon::now();

        foreach ($documents as $doc) {
            // Ekstrak 10.xxx dari URL DOI
            if (preg_match('/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i', $doc->doi, $matches)) {
                $rawDoi = $matches[0];
                
                try {
                    // Kasih delay 1 detik per dokumen biar API Crossref gak nge-blokir IP kita
                    sleep(1); 
                    
                    $response = Http::withHeaders([
                        'User-Agent' => 'SustainDex/1.0 (academic-index)'
                    ])->timeout(5)->get('https://api.crossref.org/works/' . $rawDoi);
                    
                    if ($response->successful()) {
                        $currentCitation = $response->json('message.is-referenced-by-count') ?? 0;

                        DB::beginTransaction();
                        try {
                            // 1. Update jumlah sitasi terbaru di tabel utama
                            $doc->update(['citation_count' => $currentCitation]);

                            // 2. Simpan ke tabel log (CCTV) untuk backup & bahan grafik!
                            DB::table('citation_histories')->insert([
                                'document_id' => $doc->id,
                                'citation_count' => $currentCitation,
                                'year' => $now->year,
                                'month' => $now->month,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                            DB::commit();
                            
                            $this->info("Berhasil update DOI: {$rawDoi} -> {$currentCitation} sitasi");
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error("Gagal simpan log sitasi: " . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Crossref Timeout untuk DOI: {$rawDoi}");
                }
            }
        }
        $this->info('Sinkronisasi selesai!');
    }
}