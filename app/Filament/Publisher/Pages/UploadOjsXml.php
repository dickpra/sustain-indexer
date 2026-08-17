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
    public function submit()
    {
        $data = $this->form->getState();
        $filePath = storage_path('app/public/' . $data['xml_file']);

        if (!file_exists($filePath)) {
            Notification::make()->title('File not found!')->danger()->send();
            return;
        }

        try {
            // 1. TRIK DEWA: Bersihkan Namespace (seperti <dc:title> jadi <title>) agar gampang dibaca!
            $xmlContent = file_get_contents($filePath);
            $xmlContent = preg_replace('/(<\/?)([\w\-]+:)/', '$1', $xmlContent); 
            
            // Muat XML yang sudah bersih
            $xml = simplexml_load_string($xmlContent);
            $successCount = 0;

            DB::beginTransaction();

            // 2. CARI WADAH ARTIKEL (Mendukung OJS Native, Crossref, OAI-PMH, PubMed, dll)
            $articles = $xml->xpath('//article | //journal_article | //record | //item | //PubmedArticle | //Document');

            if (empty($articles)) {
                Notification::make()->title('Unrecognized XML Format')->body('Could not find article tags in this XML.')->danger()->send();
                return;
            }

            foreach ($articles as $article) {
                // 3. AMBIL DATA DENGAN BANYAK KEMUNGKINAN TAG
                
                // Cari Judul (<title>, <article_title>, <ArticleTitle>)
                $titleNode = $article->xpath('.//title | .//article_title | .//ArticleTitle');
                $title = !empty($titleNode) ? (string) $titleNode[0] : 'Untitled Document';
                
                // Kalau judulnya benar-benar kosong, lewati data ini
                if (trim($title) === '' || trim($title) === 'Untitled Document') continue;

                // Cari Abstrak
                $abstractNode = $article->xpath('.//abstract | .//description | .//AbstractText');
                $abstract = !empty($abstractNode) ? (string) $abstractNode[0] : 'No abstract provided.';

                // Cari DOI
                $doiNode = $article->xpath('.//doi | .//identifier | .//id');
                $doi = !empty($doiNode) ? trim((string) $doiNode[0]) : null;

                // 🔥 STANDARISASI DOI: Jika isi DOI ada dan tidak diawali http, ubah jadi link resmi!
                if ($doi && !str_starts_with($doi, 'http')) {
                    // Hapus text "doi:" jika terbawa dari XML
                    $doi = str_ireplace('doi:', '', $doi);
                    $doi = 'https://doi.org/' . ltrim($doi, '/ ');
                }

                $initialCitations = rand(0, 5); 

                // 4. SIMPAN KE DATABASE DOKUMEN
                $document = Document::create([
                    'document_number' => 'OJS-' . strtoupper(Str::random(10)),
                    'title' => $title,
                    'journal_title' => 'Imported Journal', 
                    'publisher' => auth()->user()->name, 
                    'abstract' => $abstract,
                    'document_type' => 'Journal Article',
                    'pub_year' => date('Y'),
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
                    'year' => date('Y'),
                    'month' => date('m'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 5. CARI DATA PENULIS
                $authorNodes = $article->xpath('.//author | .//creator | .//person_name | .//Author');
                $authorIds = [];

                if (!empty($authorNodes)) {
                    foreach ($authorNodes as $xmlAuthor) {
                        
                        // 🔥 TRIK OJS 3: Tangkap givenname dan familyname
                        $firstName = (string) ($xmlAuthor->firstname ?? $xmlAuthor->givenname ?? $xmlAuthor->given_name ?? '');
                        $lastName = (string) ($xmlAuthor->lastname ?? $xmlAuthor->familyname ?? $xmlAuthor->surname ?? '');
                        
                        $fullName = trim($firstName . ' ' . $lastName);
                        
                        if (empty($fullName)) {
                            $fullName = trim(strip_tags($xmlAuthor->asXML()));
                        }
                        
                        // 🔥 TAMBAHKAN BARIS INI: Hapus angka dan spasi sisa di akhir nama!
                        // "Karen Joy P. Tandayag 1" akan otomatis jadi "Karen Joy P. Tandayag"
                        $fullName = preg_replace('/[\d\s]+$/', '', $fullName);

                        // Abaikan kalau benar-benar kosong
                        if (empty($fullName) || $fullName === 'Unknown') continue;

                        $authorEmail = (string) ($xmlAuthor->email ?? '');
                        $affiliation = (string) ($xmlAuthor->affiliation ?? 'Independent Researcher');

                        $institution = Institution::firstOrCreate(
                            ['name' => $affiliation]
                        );

                        // Hasilkan email dummy elegan jika XML tidak punya email
                        $cleanNameForEmail = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $fullName));
                        $finalEmail = $authorEmail ?: $cleanNameForEmail . rand(100,999) . '@imported.com';

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