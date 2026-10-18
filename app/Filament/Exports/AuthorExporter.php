<?php

namespace App\Filament\Exports;

use App\Models\Author;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AuthorExporter extends Exporter
{
    protected static ?string $model = Author::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Exports\ExportColumn::make('id')->label('ID'),
            \Filament\Actions\Exports\ExportColumn::make('name')->label('Nama Penulis'),
            \Filament\Actions\Exports\ExportColumn::make('email')->label('Email'),
            \Filament\Actions\Exports\ExportColumn::make('country')->label('Negara'),
            \Filament\Actions\Exports\ExportColumn::make('institution_id')->label('ID Institusi'),
            \Filament\Actions\Exports\ExportColumn::make('created_at')->label('Dibuat Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your author export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
