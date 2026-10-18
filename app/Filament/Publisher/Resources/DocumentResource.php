<?php

namespace App\Filament\Publisher\Resources;

use App\Filament\Publisher\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // =======================================================
    // 🔥 FITUR ISOLASI DATA (MULTI-TENANCY SEDERHANA)
    // =======================================================
   public static function getEloquentQuery(): Builder
    {
        // Publisher HANYA BISA melihat dokumen yang submitter_email-nya sama dengan email login mereka
        return parent::getEloquentQuery()
            ->with('sdgs') // 🔥 Mencegah loading tabel yang berat
            ->where('submitter_email', auth()->user()->email);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('1. Material Information')
                    ->description('Lengkapi metadata dokumen ini.')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),

                        \Filament\Forms\Components\TextInput::make('journal_title')
                            ->required(),

                        // 🔥 Nama publisher dikunci pakai nama akun yang sedang login!
                        \Filament\Forms\Components\TextInput::make('publisher')
                            ->dehydrated() // Memastikan data yang di-disable tetap tersimpan ke database
                            ->required(),

                        // ==========================================
                        // 🔥 ABSTRAK DENGAN LIVE AI TRANSLATOR & SCANNER (MURNI DARI CONFIG)
                        // ==========================================
                        Forms\Components\Textarea::make('abstract')
                            ->required()
                            ->columnSpanFull()
                            ->rows(5)
                            ->live(debounce: 2000)
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                                // Syarat 10 huruf agar gampang ditest
                                if (empty($state) || strlen($state) < 10) {
                                    $set('sdg_selections', []);
                                    return;
                                }

                                $textToScan = strtolower($state);
                                
                                // 1. Translasi AI Otomatis (Stichoza)
                                try {
                                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate('en');
                                    $translated = $tr->translate($state);
                                    if ($translated) {
                                        $textToScan = strtolower($translated);
                                    }
                                } catch (\Exception $e) {
                                    // Abaikan jika error, pakai teks asli
                                }

                                // 2. 🔥 TARIK DATA MURNI DARI CONFIG BOS
                                $dictionary = config('sdg.keywords'); 
                                
                                // Jika config kebetulan kosong/tidak terbaca, hentikan proses
                                if (!is_array($dictionary) || empty($dictionary)) {
                                    return;
                                }

                                $sdgFullNames = [
                                    'sdg1' => 'SDG 1: No Poverty', 'sdg2' => 'SDG 2: Zero Hunger', 'sdg3' => 'SDG 3: Good Health and Well-being',
                                    'sdg4' => 'SDG 4: Quality Education', 'sdg5' => 'SDG 5: Gender Equality', 'sdg6' => 'SDG 6: Clean Water and Sanitation',
                                    'sdg7' => 'SDG 7: Affordable and Clean Energy', 'sdg8' => 'SDG 8: Decent Work and Economic Growth',
                                    'sdg9' => 'SDG 9: Industry Innovation and Infrastructure', 'sdg10' => 'SDG 10: Reduced Inequality',
                                    'sdg11' => 'SDG 11: Sustainable Cities and Communities', 'sdg12' => 'SDG 12: Responsible Consumption and Production',
                                    'sdg13' => 'SDG 13: Climate Action', 'sdg14' => 'SDG 14: Life Below Water', 'sdg15' => 'SDG 15: Life on Land',
                                    'sdg16' => 'SDG 16: Peace and Justice Strong Institutions', 'sdg17' => 'SDG 17: Partnerships to achieve the Goal'
                                ];

                                $detectedSdgs = [];
                                
                                // 3. Proses pemindaian yang sudah kebal typo/spasi
                                foreach ($dictionary as $sdgId => $keywords) {
                                    // Mengubah "SDG 1", "SDG-1", atau "sdg01" murni menjadi "sdg1"
                                    $normalizedKey = strtolower(str_replace([' ', '-', ':'], '', $sdgId));
                                    $normalizedKey = str_replace('sdg0', 'sdg', $normalizedKey);
                                    
                                    if (is_array($keywords)) {
                                        foreach ($keywords as $kw) {
                                            $kw = trim(strtolower($kw));
                                            if (!empty($kw) && str_contains($textToScan, $kw)) {
                                                if (isset($sdgFullNames[$normalizedKey])) {
                                                    $detectedSdgs[] = $sdgFullNames[$normalizedKey];
                                                }
                                                break; // 🔥 1 kata kunci cocok, lencana langsung keluar!
                                            }
                                        }
                                    }
                                }
                                
                                $finalSdgs = array_values(array_unique($detectedSdgs));
                                $set('sdg_selections', $finalSdgs);
                                
                                if (count($finalSdgs) > 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('🤖 AI Auto-Scan Success')
                                        ->body('Deteksi ' . count($finalSdgs) . ' SDG berhasil.')
                                        ->success()->send();
                                }
                            }),

                        // // ==========================================
                        // // 🔥 FORM SDGs (Langsung tembak ke tabel relasi)
                        // // ==========================================
                        // \Filament\Forms\Components\CheckboxList::make('sdg_selections')
                        //     ->label('Sustainable Development Goals (SDGs)')
                        //     ->options([
                        //         "SDG 1: No Poverty" => "SDG 1: No Poverty",
                        //         "SDG 2: Zero Hunger" => "SDG 2: Zero Hunger",
                        //         "SDG 3: Good Health and Well-being" => "SDG 3: Good Health",
                        //         "SDG 4: Quality Education" => "SDG 4: Quality Education",
                        //         "SDG 5: Gender Equality" => "SDG 5: Gender Equality",
                        //         "SDG 6: Clean Water and Sanitation" => "SDG 6: Clean Water",
                        //         "SDG 7: Affordable and Clean Energy" => "SDG 7: Clean Energy",
                        //         "SDG 8: Decent Work and Economic Growth" => "SDG 8: Economic Growth",
                        //         "SDG 9: Industry Innovation and Infrastructure" => "SDG 9: Industry & Innovation",
                        //         "SDG 10: Reduced Inequality" => "SDG 10: Reduced Inequality",
                        //         "SDG 11: Sustainable Cities and Communities" => "SDG 11: Sustainable Cities",
                        //         "SDG 12: Responsible Consumption and Production" => "SDG 12: Responsible Consumption",
                        //         "SDG 13: Climate Action" => "SDG 13: Climate Action",
                        //         "SDG 14: Life Below Water" => "SDG 14: Life Below Water",
                        //         "SDG 15: Life on Land" => "SDG 15: Life on Land",
                        //         "SDG 16: Peace and Justice Strong Institutions" => "SDG 16: Peace & Justice",
                        //         "SDG 17: Partnerships to achieve the Goal" => "SDG 17: Partnerships"
                        //     ])
                        //     ->columns(3) 
                        //     ->gridDirection('row')
                        //     ->columnSpanFull()
                        //     ->dehydrated(false) // Jangan simpan ke kolom document utama
                        //     ->afterStateHydrated(function (\Filament\Forms\Components\CheckboxList $component, $state, $record) {
                        //         // SAAT EDIT DIBUKA: Tarik data dari tabel document_sdgs
                        //         if ($record) {
                        //             $component->state($record->sdgs->pluck('sdg_name')->toArray());
                        //         }
                        //     })
                        //     ->saveRelationshipsUsing(function (\Illuminate\Database\Eloquent\Model $record, $state) {
                        //         // SAAT DISIMPAN: Hapus yang lama, insert yang baru!
                        //         $record->sdgs()->delete();
                                
                        //         if (is_array($state)) {
                        //             foreach ($state as $sdgName) {
                        //                 $parts = explode(':', $sdgName);
                        //                 $record->sdgs()->create([
                        //                     'sdg_code' => trim($parts[0]),
                        //                     'sdg_name' => $sdgName
                        //                 ]);
                        //             }
                        //         }
                        //     }),

                        // // ==========================================
                        // // 🔥 FORM KEYWORD (Sekarang murni teks biasa)
                        // // ==========================================
                        // \Filament\Forms\Components\TextInput::make('keywords')
                        //     ->label('Keywords')
                        //     ->helperText('Separate with commas (e.g., carbon, climate, sustainability)')
                        //     ->columnSpanFull(),

                        // ==========================================
                        // 🔥 WIDGET BADGE SDGs (HANYA BACA / OTOMATIS)
                        // ==========================================
                        \Filament\Forms\Components\Placeholder::make('sdg_badges')
                            ->label('Sustainable Development Goals (SDGs)')
                            ->content(function (\Filament\Forms\Get $get) {
                                // Mengambil data dari hidden input secara real-time
                                $sdgs = $get('sdg_selections') ?? [];
                                
                                if (empty($sdgs)) {
                                    return new \Illuminate\Support\HtmlString('<span style="color: #888; font-style: italic;">⏳ AI is waiting for abstract to detect SDGs...</span>');
                                }

                                // Merender lencana (badge) keren jika AI menemukan SDG
                                $html = '<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 5px;">';
                                foreach ($sdgs as $sdg) {
                                    $html .= '<span style="background-color: #003366; color: white; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">🤖 ' . e($sdg) . '</span>';
                                }
                                $html .= '</div>';
                                
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),

                        // ==========================================
                        // 🔥 HIDDEN INPUT UNTUK MENYIMPAN DATA ARRAY SDG
                        // ==========================================
                        \Filament\Forms\Components\Hidden::make('sdg_selections')
                            ->dehydrated(false) // Tidak masuk ke tabel document utama
                            ->afterStateHydrated(function (\Filament\Forms\Components\Hidden $component, $record) {
                                // Saat form EDIT dibuka, panggil data SDG lama dari database
                                if ($record && $record->sdgs) {
                                    $component->state($record->sdgs->pluck('sdg_name')->toArray());
                                } else {
                                    $component->state([]);
                                }
                            })
                            ->saveRelationshipsUsing(function (\Illuminate\Database\Eloquent\Model $record, $state) {
                                // Saat form DISAVE, bersihkan SDG lama lalu suntikkan hasil AI yang baru
                                $record->sdgs()->delete();
                                
                                if (is_array($state) && count($state) > 0) {
                                    foreach ($state as $sdgName) {
                                        $parts = explode(':', $sdgName);
                                        $record->sdgs()->create([
                                            'sdg_code' => trim($parts[0]),
                                            'sdg_name' => $sdgName
                                        ]);
                                    }
                                }
                            }),

                        // ==========================================
                        // 🔥 FORM KEYWORD (Sekarang murni teks biasa)
                        // ==========================================
                        \Filament\Forms\Components\TextInput::make('keywords')
                            ->label('Keywords (Non-SDG)')
                            ->helperText('Separate with commas (e.g., carbon, climate, sustainability)')
                            ->columnSpanFull(),

                        \Filament\Forms\Components\Select::make('document_type')
                            ->options([
                                'Journal Article' => 'Journal Article',
                                'Book' => 'Book',
                                'Conference Paper' => 'Conference Paper',
                            ])
                            ->required(),

                        \Filament\Forms\Components\TextInput::make('pub_year')
                            ->numeric()
                            ->required()
                            ->default(date('Y')),

                        \Filament\Forms\Components\TextInput::make('doi')
                            ->required()
                            ->url(),

                        \Filament\Forms\Components\TextInput::make('pages')
                            ->numeric(),

                        \Filament\Forms\Components\TextInput::make('reference_count')
                            ->numeric(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('2. Authors & Affiliations')
                    ->description('Pilih penulis yang sudah ada, atau klik tanda (+) untuk menambahkan penulis & institusi baru.')
                    ->schema([
                        // ==========================================
                        // 🔥 RICH SELECT AUTHOR (SMART SEARCH + COUNTRY)
                        // ==========================================
                        \Filament\Forms\Components\Select::make('authors')
                            ->label('Authors & Affiliations')
                            ->relationship('authors', 'name')
                            ->multiple()
                            ->searchable(['name', 'email']) 
                            ->preload()
                            ->allowHtml()
                            ->getOptionLabelFromRecordUsing(function (\App\Models\Author $record) {
                                
                                // 🔒 SENSOR EMAIL
                                $emailParts = explode('@', $record->email);
                                $username = $emailParts[0] ?? 'unknown';
                                $domain = $emailParts[1] ?? 'unknown.com';
                                
                                $maskedUsername = substr($username, 0, 2) . str_repeat('*', 3);
                                $maskedEmail = $maskedUsername . '@' . $domain;

                                // 🏛️ AMBIL INSTITUSI & NEGARA
                                $institutionName = $record->institution ? $record->institution->name : 'Independent Researcher';
                                $country = $record->country ? $record->country : 'Unknown Country';

                                return "
                                    <div style='display:flex; flex-direction:column; padding: 4px 0;'>
                                        <strong style='font-size: 1rem; color: #003366;'>{$record->name}</strong>
                                        <span style='font-size: 0.85rem; color: #888;' title='Email is protected for privacy'>
                                            <i class='bi bi-shield-lock-fill text-warning'></i> 🔒 {$maskedEmail}
                                        </span>
                                        <span style='font-size: 0.8rem; color: #006600;'>
                                            🏛️ {$institutionName} <strong style='color: #cc0000;'>({$country})</strong>
                                        </span>
                                    </div>
                                ";
                            })
                            ->createOptionForm([
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->label('Full Name'),
                                
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required(),
                                
                                \Filament\Forms\Components\TextInput::make('country'),
                                
                                \Filament\Forms\Components\Select::make('institution_id')
                                    ->label('Institution')
                                    ->relationship('institution', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        \Filament\Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->label('Institution Name'),
                                        
                                        \Filament\Forms\Components\TextInput::make('country'),

                                        // 🔥 MESIN PENCARI LOKASI (CUSTOM GEOCODER)
                                        \Filament\Forms\Components\TextInput::make('search_location')
                                            ->label('Cari Lokasi Otomatis')
                                            ->placeholder('Ketik kampus / kota, klik ikon 👉')
                                            ->dehydrated(false) 
                                            ->suffixAction(
                                                \Filament\Forms\Components\Actions\Action::make('search')
                                                    ->icon('heroicon-m-magnifying-glass')
                                                    ->action(function (\Filament\Forms\Set $set, $state, $livewire) {
                                                        if (blank($state)) return;

                                                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                                                            'User-Agent' => 'SustaIndex/1.0'
                                                        ])->get('https://nominatim.openstreetmap.org/search', [
                                                            'q' => $state,
                                                            'format' => 'json',
                                                            'limit' => 1,
                                                        ]);

                                                        $results = $response->json();

                                                        if (!empty($results)) {
                                                            $lat = (float) $results[0]['lat'];
                                                            $lng = (float) $results[0]['lon'];

                                                            $set('location', ['lat' => $lat, 'lng' => $lng]);
                                                            $set('latitude', $lat);
                                                            $set('longitude', $lng);
                                                            
                                                            $livewire->dispatch('refreshMap');
                                                            
                                                            \Filament\Notifications\Notification::make()
                                                                ->title('Lokasi Ditemukan & Pin Dipindahkan!')
                                                                ->success()->send();
                                                        } else {
                                                            \Filament\Notifications\Notification::make()
                                                                ->title('Lokasi tidak ditemukan')
                                                                ->danger()->send();
                                                        }
                                                    })
                                            ),
                                        
                                        // 🔥 MAP PICKER (LEAFLET)
                                        \Dotswan\MapPicker\Fields\Map::make('location')
                                            ->label('Pin Location on Map')
                                            ->columnSpanFull()
                                            ->defaultLocation(-2.5489, 118.0149) 
                                            ->dehydrated(false) 
                                            ->afterStateUpdated(function (\Filament\Forms\Set $set, ?array $state): void {
                                                if ($state) {
                                                    $set('latitude', $state['lat']);
                                                    $set('longitude', $state['lng']);
                                                }
                                            })
                                            ->live(), 
                                            
                                        \Filament\Forms\Components\Grid::make(2)
                                            ->schema([
                                                \Filament\Forms\Components\TextInput::make('latitude')
                                                    ->label('Latitude')
                                                    ->numeric()
                                                    ->step('any') 
                                                    ->required(),
                                                    
                                                \Filament\Forms\Components\TextInput::make('longitude')
                                                    ->label('Longitude')
                                                    ->numeric()
                                                    ->step('any')
                                                    ->required(),
                                            ]),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),
                
                // ==========================================
                // 🔥 3. HIDDEN FIELDS (Kunci Keamanan)
                // ==========================================
                \Filament\Forms\Components\Hidden::make('submitter_email')
                    ->default(fn () => auth()->user()->email),
                \Filament\Forms\Components\Hidden::make('submitter_first_name')
                    ->default(fn () => auth()->user()->name),
                \Filament\Forms\Components\Hidden::make('submitter_last_name')
                    ->default('(Publisher)'),
                \Filament\Forms\Components\Hidden::make('is_verified')
                    ->default(true), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_number')->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(30),
                Tables\Columns\TextColumn::make('journal_title')->searchable(),
                Tables\Columns\TextColumn::make('publisher')->searchable(),
                // 🔥 TAMBAHKAN KOLOM BADGE SDG DI SINI
                Tables\Columns\TextColumn::make('sdgs.sdg_code')
                    ->label('SDGs')
                    ->badge()
                    ->color('info')
                    ->separator(','), // Akan menghasilkan: SDG 1, SDG 13
                Tables\Columns\TextColumn::make('document_type')->searchable(),
                Tables\Columns\TextColumn::make('pub_year')->searchable(),
                Tables\Columns\TextColumn::make('citation_count')->numeric()->sortable(),
                Tables\Columns\IconColumn::make('is_verified')->boolean(),
                Tables\Columns\TextColumn::make('views')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}