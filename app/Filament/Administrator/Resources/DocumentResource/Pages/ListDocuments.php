<?php

namespace App\Filament\Administrator\Resources\DocumentResource\Pages;

use App\Filament\Administrator\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ==========================================
            // 🔥 TOMBOL SAKTI MASS SCAN SDG (5000+ DATA)
            // ==========================================
            Actions\Action::make('scanAllSdgs')
                ->label('Scan SDG Massal')
                ->icon('heroicon-o-cpu-chip')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Mulai Pemindaian AI Massal?')
                ->modalDescription('Proses ini akan memindai ribuan abstrak dokumen sekaligus. Untuk mencegah pemblokiran dari Google, sistem hanya akan mencocokkan teks bahasa aslinya dengan kamus config bos. Dokumen yang sudah memiliki SDG akan dilewati. Lanjutkan?')
                ->action(function () {
                    
                    // 1. Ambil SEMUA dokumen yang punya abstrak TAPI belum punya SDG
                    $documents = Document::doesntHave('sdgs')
                                    ->whereNotNull('abstract')
                                    ->get();

                    // 2. Tarik Kamus Config
                    $dictionary = config('sdg.keywords');
                    if (!is_array($dictionary) || empty($dictionary)) {
                        Notification::make()->title('Gagal')->body('Kamus SDG di config kosong!')->danger()->send();
                        return;
                    }

                    $sdgFullNames = [
                        'sdg1' => 'SDG 1: No Poverty', 'sdg2' => 'SDG 2: Zero Hunger', 'sdg3' => 'SDG 3: Good Health and Well-being',
                        'sdg4' => 'SDG 4: Quality Education', 'sdg5' => 'SDG 5: Gender Equality', 'sdg6' => 'SDG 6: Clean Water and Sanitation',
                        'sdg7' => 'SDG 7: Affordable and Clean Energy', 'sdg8' => 'SDG 8: Decent Work and Economic Growth',
                        'sdg9' => 'SDG 9: Industry Innovation and Infrastructure', 'sdg10' => 'SDG 10: Reduced Inequality',
                        'sdg11' => 'SDG 11: Sustainable Cities and Communities', 'sdg12' => 'SDG 12: Responsible Consumption and Production',
                        'sdg13' => 'SDG 13: Climate Action', 'sdg14' => 'SDG 14: Life Below Water', 'sdg15' => 'SDG 15: Life on Land',
                        'sdg16' => 'SDG 16: Peace and Justice Strong Institutions', 'sdg17' => 'SDG 17: Partnerships to achieve the Goal'
                    ];

                    $scannedCount = 0;
                    $foundCount = 0;
                    
                    // Siapkan laci penyimpanan massal agar database tidak jebol
                    $insertData = [];

                    // 3. Mulai Perulangan Super Cepat (Array Processing)
                    foreach ($documents as $doc) {
                        $textToScan = strtolower($doc->abstract);
                        $docHasSdg = false;

                        foreach ($dictionary as $sdgId => $keywords) {
                            $normalizedKey = strtolower(str_replace([' ', '-', ':'], '', $sdgId));
                            $normalizedKey = str_replace('sdg0', 'sdg', $normalizedKey);
                            
                            if (is_array($keywords)) {
                                foreach ($keywords as $kw) {
                                    $kw = trim(strtolower($kw));
                                    if (!empty($kw) && str_contains($textToScan, $kw)) {
                                        
                                        // Jika cocok, masukkan ke laci penyimpanan
                                        $insertData[] = [
                                            'document_id' => $doc->id,
                                            'sdg_code' => strtoupper(str_replace('sdg', 'SDG ', $normalizedKey)), // Menjadi "SDG 1"
                                            'sdg_name' => $sdgFullNames[$normalizedKey] ?? strtoupper($normalizedKey),
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ];
                                        $docHasSdg = true;
                                        break; // Cukup 1 kata cocok untuk SDG ini, lanjut cari SDG lain
                                    }
                                }
                            }
                        }

                        if ($docHasSdg) {
                            $foundCount++;
                        }
                        $scannedCount++;
                    }

                    // 4. Masukkan ribuan data ke tabel document_sdgs secara aman (di-pecah per 500 baris)
                    if (!empty($insertData)) {
                        $chunks = array_chunk($insertData, 500);
                        foreach ($chunks as $chunk) {
                            DB::table('document_sdgs')->insert($chunk);
                        }
                    }

                    // 5. Tampilkan Notifikasi Keberhasilan
                    Notification::make()
                        ->title('Scan Massal Selesai! 🚀')
                        ->body("Memindai $scannedCount dokumen. Ditemukan $foundCount dokumen yang mendapatkan lencana SDG baru.")
                        ->success()
                        ->send();
                }),

            // Tombol bawaan untuk membuat dokumen baru
            Actions\CreateAction::make(),
        ];
    }
}