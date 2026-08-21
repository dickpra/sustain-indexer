<?php

namespace App\Filament\Publisher\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Document;

class PublisherStatsOverview extends BaseWidget
{
    // Supaya widget ini muncul paling atas!
    protected static ?int $sort = 1; 

    protected function getStats(): array
    {
        // 🔒 Kunci data HANYA untuk publisher yang sedang login
        $userEmail = auth()->user()->email;
        
        $totalDocs = Document::where('submitter_email', $userEmail)->count();
        $totalViews = Document::where('submitter_email', $userEmail)->sum('views');
        $totalCitations = Document::where('submitter_email', $userEmail)->sum('citation_count');

        return [
            Stat::make('Total Publications', number_format($totalDocs))
                ->description('All published papers in SustaIndex')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart([2, 3, 5, 7, 10, 15, 20]), // Grafik sparkline (pemanis)

            Stat::make('Global Readership', number_format($totalViews))
                ->description('Total views across all papers')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), 

            Stat::make('Impact Factor', number_format($totalCitations))
                ->description('Total citations received')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('warning')
                ->chart([1, 4, 2, 8, 12, 10, 15]), 
        ];
    }
}