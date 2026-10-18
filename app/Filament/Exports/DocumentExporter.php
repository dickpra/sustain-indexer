<?php

namespace App\Filament\Exports;

use App\Models\Document;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DocumentExporter extends Exporter
{
    protected static ?string $model = Document::class;

    public static function getColumns(): array
    {
        return [
            \Filament\Actions\Exports\ExportColumn::make('id')->label('ID'),
            \Filament\Actions\Exports\ExportColumn::make('document_number')->label('No Dokumen'),
            \Filament\Actions\Exports\ExportColumn::make('title')->label('Judul'),
            \Filament\Actions\Exports\ExportColumn::make('journal_title')->label('Judul Jurnal'),
            \Filament\Actions\Exports\ExportColumn::make('publisher')->label('Penerbit'),
            \Filament\Actions\Exports\ExportColumn::make('abstract')->label('Abstrak'),
            \Filament\Actions\Exports\ExportColumn::make('keywords')->label('Keywords'),
            \Filament\Actions\Exports\ExportColumn::make('document_type')->label('Tipe'),
            \Filament\Actions\Exports\ExportColumn::make('pub_year')->label('Tahun'),
            \Filament\Actions\Exports\ExportColumn::make('pages')->label('Halaman'),
            \Filament\Actions\Exports\ExportColumn::make('doi')->label('DOI'),
            \Filament\Actions\Exports\ExportColumn::make('reference_count')->label('Jml Referensi'),
            \Filament\Actions\Exports\ExportColumn::make('citation_count')->label('Jml Sitasi'),
            \Filament\Actions\Exports\ExportColumn::make('views')->label('Views'),
            \Filament\Actions\Exports\ExportColumn::make('is_peer_reviewed')->label('Peer Reviewed?'),
            \Filament\Actions\Exports\ExportColumn::make('is_verified')->label('Verified?'),
            \Filament\Actions\Exports\ExportColumn::make('submitter_first_name')->label('Nama Depan Submitter'),
            \Filament\Actions\Exports\ExportColumn::make('submitter_last_name')->label('Nama Blkng Submitter'),
            \Filament\Actions\Exports\ExportColumn::make('submitter_email')->label('Email Submitter'),
            \Filament\Actions\Exports\ExportColumn::make('created_at')->label('Dibuat Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export dokumen berhasil! Total ' . number_format($export->successful_rows) . ' baris data telah diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' Namun, ada ' . number_format($failedRowsCount) . ' baris yang gagal diekspor.';
        }

        return $body;
    }
}