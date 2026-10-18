<?php

namespace App\Filament\Exports;

use App\Models\Institution;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class InstitutionExporter extends Exporter
{
    protected static ?string $model = Institution::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Exports\ExportColumn::make('id')->label('ID'),
            \Filament\Actions\Exports\ExportColumn::make('name')->label('Nama Institusi'),
            \Filament\Actions\Exports\ExportColumn::make('country')->label('Negara'),
            \Filament\Actions\Exports\ExportColumn::make('latitude')->label('Garis Lintang (Lat)'),
            \Filament\Actions\Exports\ExportColumn::make('longitude')->label('Garis Bujur (Long)'),
            \Filament\Actions\Exports\ExportColumn::make('created_at')->label('Dibuat Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your institution export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
