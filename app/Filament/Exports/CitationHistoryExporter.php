<?php

namespace App\Filament\Exports;

use App\Models\CitationHistory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CitationHistoryExporter extends Exporter
{
    protected static ?string $model = CitationHistory::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Exports\ExportColumn::make('id')->label('ID'),
            \Filament\Actions\Exports\ExportColumn::make('document_id')->label('ID Dokumen'),
            \Filament\Actions\Exports\ExportColumn::make('citation_count')->label('Jumlah Sitasi'),
            \Filament\Actions\Exports\ExportColumn::make('year')->label('Tahun'),
            \Filament\Actions\Exports\ExportColumn::make('month')->label('Bulan'),
            \Filament\Actions\Exports\ExportColumn::make('created_at')->label('Dicatat Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your citation history export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
