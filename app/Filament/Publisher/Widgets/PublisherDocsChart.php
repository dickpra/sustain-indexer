<?php

namespace App\Filament\Publisher\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class PublisherDocsChart extends ChartWidget
{
    protected static ?string $heading = 'Publications per Year';
    
    // Taruh di bawah kartu Stats Overview
    protected static ?int $sort = 2; 
    
    // Agar ukurannya tidak memakan 1 layar penuh (setengah layar saja)
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $userEmail = auth()->user()->email;
        
        // Ambil jumlah dokumen per tahun dari database
        $data = Document::where('submitter_email', $userEmail)
            ->select('pub_year', DB::raw('count(*) as total'))
            ->groupBy('pub_year')
            ->orderBy('pub_year', 'asc')
            ->pluck('total', 'pub_year')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Published Papers',
                    'data' => array_values($data),
                    'backgroundColor' => '#0d6efd',
                    'borderColor' => '#003366',
                    'borderWidth' => 2,
                    'borderRadius' => 4, // Bikin ujung bar-nya agak membulat (modern)
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Bisa diganti 'line' kalau bos lebih suka grafik garis!
    }
}