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
    protected $signature = 'sustaindex:sync-citations';
    protected $description = 'Weekly sync of citations and DOI auditing';

    public function handle()
    {
        $this->info('Memulai sinkronisasi dan audit DOI...');

        // 🔥 AMBIL SEMUA DOKUMEN YANG STATUSNYA MASIH AKTIF (VERIFIED)
        $documents = Document::where('is_verified', true)->get();
        $now = Carbon::now();

        foreach ($documents as $doc) {
            // 1. CEK: Apakah DOI kosong atau formatnya ngawur?
            if (empty($doc->doi) || !preg_match('/10\.\d{4,9}\/[-._;()\/:A-Z0-9]+/i', $doc->doi, $matches)) {
                $this->rejectDocument($doc, 'DOI kosong atau format tidak valid.');
                continue; // Lanjut ke dokumen berikutnya
            }

            $rawDoi = $matches[0];
            
            try {
                // Delay 1 detik biar IP server tidak diblokir Crossref
                sleep(1); 
                
                $response = Http::withHeaders([
                    'User-Agent' => 'SustaIndex/1.0 (academic-index)'
                ])->timeout(5)->get('https://api.crossref.org/works/' . $rawDoi);
                
                if ($response->successful()) {
                    // 🔥 DOI VALID! Ambil sitasinya
                    $currentCitation = $response->json('message.is-referenced-by-count') ?? 0;

                    DB::beginTransaction();
                    try {
                        $doc->update(['citation_count' => $currentCitation]);

                        DB::table('citation_histories')->insert([
                            'document_id' => $doc->id,
                            'citation_count' => $currentCitation,
                            'year' => $now->year,
                            'month' => $now->month,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        DB::commit();
                        
                        $this->info("✅ Sukses: {$rawDoi} -> {$currentCitation} sitasi");
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Gagal simpan log sitasi: " . $e->getMessage());
                    }
                } 
                // 2. CEK: Apakah Crossref bilang DOI ini tidak ada? (Error 404)
                elseif ($response->status() === 404) {
                    $this->rejectDocument($doc, "DOI tidak ditemukan di database Crossref: {$rawDoi}");
                } 
                // Jika error 500 (Server Crossref Down), jangan ditolak, biarkan saja
                else {
                    Log::warning("Crossref Error ({$response->status()}) untuk DOI: {$rawDoi}");
                }
            } catch (\Exception $e) {
                Log::warning("Crossref Timeout untuk DOI: {$rawDoi}");
            }
        }
        $this->info('Sinkronisasi dan Audit selesai!');
    }

    // Fungsi khusus untuk mencabut status dokumen
    private function rejectDocument($doc, $reason)
    {
        // Ubah is_verified jadi false agar hilang dari halaman depan (Index)
        $doc->update([
            'is_verified' => false, 
        ]);
        
        $this->warn("❌ Ditolak Otomatis: Dokumen ID {$doc->id} - {$reason}");
        Log::info("Dokumen ID {$doc->id} ditolak otomatis oleh sistem: {$reason}");
    }
}