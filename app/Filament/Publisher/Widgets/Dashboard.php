<?php

namespace App\Filament\Publisher\Widgets;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Admin\Widgets\GlobalStatsOverview;
use App\Filament\Admin\Widgets\UserCountryChart;
use App\Filament\Admin\Widgets\SubmissionTrendChart;
use App\Filament\Admin\Widgets\VisitorChart;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            PublisherStatsOverview::make(),
            PublisherDocsChart::make(),  
        ];
    }
}
