<?php

namespace App\Filament\Administrator\Resources;

use App\Filament\Administrator\Resources\DocumentResource\Pages;
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
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Indexing Management';

    // 🔥 Mencegah N+1 Query agar tabel admin tidak lemot
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['sdgs', 'authors']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('1. Material Information')
                    ->schema([
                        Forms\Components\TextInput::make('document_number')
                            ->required()
                            // Admin bisa mengedit nomor dokumen jika sangat mendesak
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('journal_title'),
                        Forms\Components\TextInput::make('publisher'),

                        // ==========================================
                        // 🔥 ABSTRAK DENGAN LIVE AI TRANSLATOR & SCANNER
                        // ==========================================
                        Forms\Components\Textarea::make('abstract')
                            ->required()
                            ->columnSpanFull()
                            ->rows(5)
                            ->live(debounce: 2000) // Menunggu 2 detik setelah admin mengetik
                            ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                                if (empty($state) || strlen($state) < 30) {
                                    $set('sdg_selections', []);
                                    return;
                                }

                                $textToScan = strtolower($state);
                                try {
                                    $tr = new \Stichoza\GoogleTranslate\GoogleTranslate('en');
                                    $translated = $tr->translate($state);
                                    if ($translated) {
                                        $textToScan = strtolower($translated);
                                    }
                                } catch (\Exception $e) {
                                    // Abaikan jika error, pakai teks asli
                                }

                                $dictionary = config('sdg.keywords');
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
                                if ($dictionary) {
                                    foreach ($dictionary as $sdgId => $keywords) {
                                        $matchCount = 0;
                                        foreach ($keywords as $kw) {
                                            if (str_contains($textToScan, strtolower($kw))) {
                                                $matchCount++;
                                                if ($matchCount >= 2) {
                                                    $key = strtolower($sdgId);
                                                    if (isset($sdgFullNames[$key])) {
                                                        $detectedSdgs[] = $sdgFullNames[$key];
                                                    }
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                $set('sdg_selections', $detectedSdgs);
                                if (count($detectedSdgs) > 0) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('🤖 AI Auto-Scan Success')
                                        ->body('Deteksi ulang SDG berhasil.')
                                        ->success()->send();
                                }
                            }),

                        // ==========================================
                        // 🔥 WIDGET BADGE SDGs (HANYA BACA)
                        // ==========================================
                        \Filament\Forms\Components\Placeholder::make('sdg_badges')
                            ->label('Sustainable Development Goals (SDGs)')
                            ->content(function (\Filament\Forms\Get $get) {
                                $sdgs = $get('sdg_selections') ?? [];
                                if (empty($sdgs)) {
                                    return new \Illuminate\Support\HtmlString('<span style="color: #888; font-style: italic;">⏳ Menunggu teks abstrak...</span>');
                                }
                                $html = '<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 5px;">';
                                foreach ($sdgs as $sdg) {
                                    $html .= '<span style="background-color: #003366; color: white; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: bold;">🤖 ' . e($sdg) . '</span>';
                                }
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),

                        // ==========================================
                        // 🔥 HIDDEN INPUT (Simpan relasi SDG ke database)
                        // ==========================================
                        \Filament\Forms\Components\Hidden::make('sdg_selections')
                            ->dehydrated(false)
                            ->afterStateHydrated(function (\Filament\Forms\Components\Hidden $component, $record) {
                                if ($record && $record->sdgs) {
                                    $component->state($record->sdgs->pluck('sdg_name')->toArray());
                                } else {
                                    $component->state([]);
                                }
                            })
                            ->saveRelationshipsUsing(function (\Illuminate\Database\Eloquent\Model $record, $state) {
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

                        \Filament\Forms\Components\TextInput::make('keywords')
                            ->label('Keywords (Non-SDG)')
                            ->helperText('Pisahkan dengan koma')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('document_type')
                            ->options([
                                'Journal Article' => 'Journal Article',
                                'Book' => 'Book',
                                'Conference Paper' => 'Conference Paper',
                            ]),
                            
                        Forms\Components\TextInput::make('pub_year'),
                        Forms\Components\TextInput::make('pages')->numeric(),
                        Forms\Components\TextInput::make('reference_count')->numeric(),
                        Forms\Components\TextInput::make('doi')->url()->columnSpanFull(),
                    ])->columns(2),

                \Filament\Forms\Components\Section::make('2. Authors Relation')
                    ->schema([
                        // Tampilan Relasi Author yang lebih informatif untuk Admin
                        \Filament\Forms\Components\Select::make('authors')
                            ->relationship('authors', 'name')
                            ->multiple()
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->allowHtml()
                            ->getOptionLabelFromRecordUsing(function (\App\Models\Author $record) {
                                $institutionName = $record->institution ? $record->institution->name : 'No Institution';
                                return "
                                    <div style='display:flex; flex-direction:column;'>
                                        <strong style='color: #003366;'>{$record->name}</strong>
                                        <span style='font-size: 0.85rem; color: #666;'>📧 {$record->email}</span>
                                        <span style='font-size: 0.8rem; color: #006600;'>🏛️ {$institutionName}</span>
                                    </div>
                                ";
                            })
                            ->columnSpanFull(),
                    ]),

                \Filament\Forms\Components\Section::make('3. Administration & Verification')
                    ->schema([
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verified (Tampil di Publik)')
                            ->onColor('success')
                            ->offColor('danger')
                            ->required(),
                            
                        Forms\Components\Toggle::make('is_peer_reviewed'),

                        Forms\Components\TextInput::make('citation_count')->numeric(),
                        Forms\Components\TextInput::make('views')->numeric(),

                        // 🔥 ADMIN KINI BISA MENGEDIT DATA SUBMITTER JIKA ADA KESALAHAN TYPO
                        Forms\Components\Fieldset::make('Submitter Info')
                            ->schema([
                                Forms\Components\TextInput::make('submitter_first_name'),
                                Forms\Components\TextInput::make('submitter_last_name'),
                                Forms\Components\TextInput::make('submitter_email')->email(),
                            ])->columns(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_number')->searchable(),
                Tables\Columns\TextColumn::make('title')->limit(30)->searchable(),
                Tables\Columns\TextColumn::make('publisher')->searchable(),
                
                // 🔥 ADMIN BISA MELIHAT BADGE SDG LANGSUNG DI TABEL
                Tables\Columns\TextColumn::make('sdgs.sdg_code')
                    ->label('SDGs')
                    ->badge()
                    ->color('info')
                    ->separator(','),

                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label('Verified'),
                    
                Tables\Columns\TextColumn::make('submitter_email')
                    ->searchable()
                    ->toggleable()
                    ->copyable() // Fitur copy email sekali klik
                    ->copyMessage('Email disalin!'),
                    
                Tables\Columns\TextColumn::make('pub_year')->sortable(),
                Tables\Columns\TextColumn::make('citation_count')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
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