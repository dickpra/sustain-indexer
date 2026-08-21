<?php

namespace App\Filament\Publisher\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Document;
use App\Models\Author;
use App\Models\Institution;
use Illuminate\Support\Str;

class UploadOjsXml extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-up';
    protected static ?string $navigationLabel = 'Upload OJS XML';
    protected static ?string $title = 'Mass Indexing via OJS XML';
    protected static ?int $navigationSort = 2; // Taruh di bawah menu Documents

    protected static string $view = 'filament.publisher.pages.upload-ojs-xml';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Upload OJS Native XML')
                    ->description('Upload your exported XML file from Open Journal Systems (OJS) here. Our engine will automatically extract titles, abstracts, DOIs, and authors, then index them into your SustaIndex publisher profile.')
                    ->schema([
                        FileUpload::make('xml_file')
                            ->label('Select XML File')
                            ->acceptedFileTypes(['application/xml', 'text/xml'])
                            ->directory('ojs-uploads')
                            ->required()
                            ->helperText('Max file size: 10MB. Ensure it is a valid OJS Native XML export.'),
                    ])
            ])
            ->statePath('data');
    }

    // Tombol Eksekusi
    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Scan & Index XML')
                ->submit('submit')
                ->color('primary')
                ->icon('heroicon-o-cpu-chip'),
        ];
    }

    // =======================================================
    // 🔥 MESIN PEMBACA XML "SAPU JAGAT" (MULTI-FORMAT)
    // =======================================================
    // =======================================================
    // 🔥 MESIN PEMBACA XML "SAPU JAGAT" (MULTI-FORMAT)
    // =======================================================
    public function submit()
    {
        $data = $this->form->getState();
        $filePath = storage_path('app/public/' . $data['xml_file']);

        if (!file_exists($filePath)) {
            Notification::make()->title('File not found!')->danger()->send();
            return;
        }

        try {
            // 1. TRIK DEWA: Bersihkan Namespace
            $xmlContent = file_get_contents($filePath);
            $xmlContent = preg_replace('/(<\/?)([\w\-]+:)/', '$1', $xmlContent); 
            
            $xml = simplexml_load_string($xmlContent);
            $successCount = 0;

            DB::beginTransaction();

            // 2. CARI WADAH ARTIKEL
            $articles = $xml->xpath('//article | //journal_article | //record | //item | //PubmedArticle | //Document');

            if (empty($articles)) {
                Notification::make()->title('Unrecognized XML Format')->body('Could not find article tags in this XML.')->danger()->send();
                return;
            }

            foreach ($articles as $article) {
                // 3. AMBIL DATA DENGAN BANYAK KEMUNGKINAN TAG
                
                // Cari Judul
                $titleNode = $article->xpath('.//title | .//article_title | .//ArticleTitle');
                $title = !empty($titleNode) ? (string) $titleNode[0] : 'Untitled Document';
                if (trim($title) === '' || trim($title) === 'Untitled Document') continue;

                // Cari Abstrak
                $abstractNode = $article->xpath('.//abstract | .//description | .//AbstractText');
                $abstract = !empty($abstractNode) ? (string) $abstractNode[0] : 'No abstract provided.';

                // Cari DOI
                $doiNode = $article->xpath('.//doi | .//identifier | .//id');
                $doi = !empty($doiNode) ? trim((string) $doiNode[0]) : null;
                if ($doi && !str_starts_with($doi, 'http')) {
                    $doi = str_ireplace('doi:', '', $doi);
                    $doi = 'https://doi.org/' . ltrim($doi, '/ ');
                }

                // 🔥 BARU: Cari Journal Title
                $journalNode = $article->xpath('.//journalTitle | .//journal_title | .//JournalTitle');
                $journalTitle = !empty($journalNode) ? (string) $journalNode[0] : 'Imported Journal';

                // 🔥 BARU: Cari Publisher
                $publisherNode = $article->xpath('.//publisher | .//Publisher');
                $publisherName = !empty($publisherNode) ? (string) $publisherNode[0] : auth()->user()->name;

                // 🔥 BARU: Cari Tahun Publikasi dari publicationDate (misal: 2025-04-30 -> ambil 2025)
                $pubDateNode = $article->xpath('.//publicationDate | .//date | .//year');
                $pubYear = date('Y'); // default tahun ini
                if (!empty($pubDateNode)) {
                    $pubYear = date('Y', strtotime((string) $pubDateNode[0]));
                }

                $initialCitations = rand(0, 5); 

                // 4. SIMPAN KE DATABASE DOKUMEN
                $document = Document::create([
                    'document_number' => 'OJS-' . strtoupper(Str::random(10)),
                    'title' => $title,
                    'journal_title' => $journalTitle, // Menggunakan data XML
                    'publisher' => $publisherName,    // Menggunakan data XML
                    'abstract' => $abstract,
                    'document_type' => 'Journal Article',
                    'pub_year' => $pubYear,           // Menggunakan data XML
                    'doi' => $doi,
                    'is_verified' => true,
                    'views' => 0,
                    'citation_count' => $initialCitations,
                    'submitter_first_name' => auth()->user()->name,
                    'submitter_last_name' => '(Publisher)',
                    'submitter_email' => auth()->user()->email, 
                ]);

                DB::table('citation_histories')->insert([
                    'document_id' => $document->id,
                    'citation_count' => $initialCitations,
                    'year' => $pubYear,
                    'month' => date('m'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 5. CARI DATA PENULIS
                $authorNodes = $article->xpath('.//author | .//creator | .//person_name | .//Author');
                $authorIds = [];

                if (!empty($authorNodes)) {
                    foreach ($authorNodes as $xmlAuthor) {
                        
                        // 🔥 REVISI: Tangkap nama dari tag <name> (Sesuai format XML DOAJ)
                        $nameNode = $xmlAuthor->xpath('.//name');
                        if (!empty($nameNode)) {
                            $fullName = (string) $nameNode[0];
                        } else {
                            // Fallback jika pakai OJS Native biasa
                            $firstName = (string) ($xmlAuthor->firstname ?? $xmlAuthor->givenname ?? $xmlAuthor->given_name ?? '');
                            $lastName = (string) ($xmlAuthor->lastname ?? $xmlAuthor->familyname ?? $xmlAuthor->surname ?? '');
                            $fullName = trim($firstName . ' ' . $lastName);
                        }
                        
                        $fullName = preg_replace('/[\d\s]+$/', '', $fullName);
                        if (empty($fullName) || $fullName === 'Unknown') continue;

                        // 🔥 REVISI: Ambil Affiliation berdasarkan <affiliationId>
                        $affiliation = 'Independent Researcher';
                        $affilIdNode = $xmlAuthor->xpath('.//affiliationId');
                        
                        if (!empty($affilIdNode)) {
                            $affilId = (string) $affilIdNode[0];
                            // Cari nama kampus di dalam <affiliationsList> menggunakan XPath berdasarkan ID
                            $affilNameNode = $article->xpath(".//affiliationsList/affiliationName[@affiliationId='{$affilId}']");
                            if (!empty($affilNameNode)) {
                                $affiliation = (string) $affilNameNode[0];
                            }
                        }

                        // Simpan / Ambil Institusi
                        $institution = Institution::firstOrCreate(
                            ['name' => $affiliation]
                        );

                        // Email Generator
                        $authorEmail = (string) ($xmlAuthor->email ?? '');
                        $cleanNameForEmail = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $fullName));
                        $finalEmail = $authorEmail ?: $cleanNameForEmail . rand(100,999) . '@imported.com';

                        // Simpan / Ambil Author
                        $author = Author::firstOrCreate(
                            ['email' => $finalEmail],
                            [
                                'name' => $fullName,
                                'institution_id' => $institution->id,
                            ]
                        );

                        $authorIds[] = $author->id;
                    }
                }

                if (!empty($authorIds)) {
                    $document->authors()->attach($authorIds);
                }

                $successCount++;
            }

            DB::commit();

            Notification::make()
                ->title('Indexing Complete!')
                ->body("Successfully extracted and indexed {$successCount} documents.")
                ->success()
                ->send();

            $this->form->fill();

        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Error Processing XML')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}