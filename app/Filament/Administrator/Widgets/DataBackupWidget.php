<?php

namespace App\Filament\Administrator\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms; 

use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;

// Panggil Exporter
use App\Filament\Exports\DocumentExporter;
use App\Filament\Exports\AuthorExporter;
use App\Filament\Exports\InstitutionExporter;

// Panggil Importer
use App\Filament\Imports\DocumentImporter;
use App\Filament\Imports\AuthorImporter;
use App\Filament\Imports\InstitutionImporter;

class DataBackupWidget extends Widget implements HasActions, HasForms 
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $view = 'filament.administrator.widgets.data-backup-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 1;

    // =====================================
    // 📤 TOMBOL EXPORT
    // =====================================
    public function exportDocumentsAction(): \Filament\Actions\Action {
        return ExportAction::make('exportDocuments')->label('Export Dokumen')->icon('heroicon-o-arrow-down-tray')->color('primary')->exporter(DocumentExporter::class);
    }
    public function exportAuthorsAction(): \Filament\Actions\Action {
        return ExportAction::make('exportAuthors')->label('Export Penulis')->icon('heroicon-o-arrow-down-tray')->color('success')->exporter(AuthorExporter::class);
    }
    public function exportInstitutionsAction(): \Filament\Actions\Action {
        return ExportAction::make('exportInstitutions')->label('Export Institusi')->icon('heroicon-o-arrow-down-tray')->color('warning')->exporter(InstitutionExporter::class);
    }

    // =====================================
    // 📥 TOMBOL IMPORT
    // =====================================
    public function importDocumentsAction(): \Filament\Actions\Action {
        return ImportAction::make('importDocuments')->label('Import Dokumen')->icon('heroicon-o-arrow-up-tray')->color('primary')->importer(DocumentImporter::class);
    }
    public function importAuthorsAction(): \Filament\Actions\Action {
        return ImportAction::make('importAuthors')->label('Import Penulis')->icon('heroicon-o-arrow-up-tray')->color('success')->importer(AuthorImporter::class);
    }
    public function importInstitutionsAction(): \Filament\Actions\Action {
        return ImportAction::make('importInstitutions')->label('Import Institusi')->icon('heroicon-o-arrow-up-tray')->color('warning')->importer(InstitutionImporter::class);
    }
}