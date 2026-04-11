<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Document; // <-- Wajib dipanggil
use Carbon\Carbon;       // <-- Wajib dipanggil untuk manipulasi waktu
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // 👇 LOGIKA TUKANG SAPU OTOMATIS 👇
        $schedule->call(function () {
            // Cari dokumen yang belum diverifikasi & umurnya sudah lewat 3 hari
            $expiredDocuments = Document::where('is_verified', false)
                                ->where('created_at', '<', Carbon::now()->subDays(3))
                                ->get();

            // Hapus satu per satu
            foreach ($expiredDocuments as $doc) {
                $doc->delete();
            }

            // Catat di Log
            Log::info("Tukang Sapu: Berhasil menghapus " . $expiredDocuments->count() . " dokumen kadaluarsa yang tidak diverifikasi.");
            
        })->daily(); // Dijalankan sehari sekali
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}