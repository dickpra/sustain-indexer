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
        return parent::getEloquentQuery()->where('submitter_email', auth()->user()->email);
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

                        \Filament\Forms\Components\Textarea::make('abstract')
                            ->required()
                            ->columnSpanFull()
                            ->rows(5),

                        // ==========================================
                        // 🔥 TRIK SIHIR: GABUNGAN KEYWORD & SDGS
                        // ==========================================
                        \Filament\Forms\Components\CheckboxList::make('sdg_selections')
                            ->label('Sustainable Development Goals (SDGs)')
                            ->options([
                                "SDG 1: No Poverty" => "SDG 1: No Poverty",
                                "SDG 2: Zero Hunger" => "SDG 2: Zero Hunger",
                                "SDG 3: Good Health and Well-being" => "SDG 3: Good Health",
                                "SDG 4: Quality Education" => "SDG 4: Quality Education",
                                "SDG 5: Gender Equality" => "SDG 5: Gender Equality",
                                "SDG 6: Clean Water and Sanitation" => "SDG 6: Clean Water",
                                "SDG 7: Affordable and Clean Energy" => "SDG 7: Clean Energy",
                                "SDG 8: Decent Work and Economic Growth" => "SDG 8: Economic Growth",
                                "SDG 9: Industry, Innovation and Infrastructure" => "SDG 9: Industry & Innovation",
                                "SDG 10: Reduced Inequality" => "SDG 10: Reduced Inequality",
                                "SDG 11: Sustainable Cities and Communities" => "SDG 11: Sustainable Cities",
                                "SDG 12: Responsible Consumption and Production" => "SDG 12: Responsible Consumption",
                                "SDG 13: Climate Action" => "SDG 13: Climate Action",
                                "SDG 14: Life Below Water" => "SDG 14: Life Below Water",
                                "SDG 15: Life on Land" => "SDG 15: Life on Land",
                                "SDG 16: Peace and Justice Strong Institutions" => "SDG 16: Peace & Justice",
                                "SDG 17: Partnerships to achieve the Goal" => "SDG 17: Partnerships"
                            ])
                            ->columns(3) 
                            ->gridDirection('row')
                            ->columnSpanFull()
                            ->dehydrated(false)
                            ->default([]) // 🔥 OBAT 1: Paksa jadi array kosong secara default
                            ->afterStateHydrated(function (\Filament\Forms\Components\CheckboxList $component, $state, $record) {
                                // Saat form EDIT dibuka, ekstrak SDG dari dalam string keywords
                                if ($record && $record->keywords) {
                                    $keywordsArray = array_map('trim', explode(',', $record->keywords));
                                    $sdgs = array_filter($keywordsArray, fn($kw) => str_starts_with($kw, 'SDG'));
                                    $component->state(array_values($sdgs));
                                } else {
                                    // 🔥 OBAT 2: Kalau bikin baru (Create), pastikan statusnya array kosong!
                                    $component->state([]); 
                                }
                            }),

                        \Filament\Forms\Components\TextInput::make('keywords')
                            ->label('Keywords (Non-SDG)')
                            ->helperText('Separate with commas (e.g., carbon, climate, sustainability)')
                            ->columnSpanFull()
                            ->afterStateHydrated(function (\Filament\Forms\Components\TextInput $component, $state, $record) {
                                // Saat form EDIT dibuka, pisahkan keyword murni dari SDG
                                if ($record && $record->keywords) {
                                    $keywordsArray = array_map('trim', explode(',', $record->keywords));
                                    $pureKeywords = array_filter($keywordsArray, fn($kw) => !str_starts_with($kw, 'SDG'));
                                    $component->state(implode(', ', $pureKeywords));
                                }
                            })
                            ->dehydrateStateUsing(function ($state, \Filament\Forms\Get $get) {
                                // SAAT DISAVE: Gabungkan input text keywords + centangan SDGs
                                $sdgs = $get('sdg_selections') ?? [];
                                $pureKeywords = array_filter(array_map('trim', explode(',', $state)));
                                $finalKeywords = array_merge($pureKeywords, $sdgs);
                                
                                return implode(', ', $finalKeywords);
                            }),
                        // ==========================================

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