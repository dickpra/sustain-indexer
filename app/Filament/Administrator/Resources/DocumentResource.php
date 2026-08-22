<?php

namespace App\Filament\Administrator\Resources;

use App\Filament\Administrator\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationGroup = 'Indexing Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('1. Material Information')
                    ->schema([
                        Forms\Components\TextInput::make('document_number')
                            ->required()
                            ->disabled() // Nomor dokumen biar ga bisa diubah sembarangan
                            ->dehydrated()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('journal_title'),
                        Forms\Components\TextInput::make('publisher'),

                        Forms\Components\Textarea::make('abstract')
                            ->required()
                            ->columnSpanFull()
                            ->rows(4),

                        // 🔥 TRIK SIHIR SDGs (Sama kayak Publisher)
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
                            ->default([])
                            ->afterStateHydrated(function (\Filament\Forms\Components\CheckboxList $component, $state, $record) {
                                if ($record && $record->keywords) {
                                    $keywordsArray = array_map('trim', explode(',', $record->keywords));
                                    $sdgs = array_filter($keywordsArray, fn($kw) => str_starts_with($kw, 'SDG'));
                                    $component->state(array_values($sdgs));
                                } else {
                                    $component->state([]);
                                }
                            }),

                        \Filament\Forms\Components\TextInput::make('keywords')
                            ->label('Keywords (Non-SDG)')
                            ->columnSpanFull()
                            ->afterStateHydrated(function (\Filament\Forms\Components\TextInput $component, $state, $record) {
                                if ($record && $record->keywords) {
                                    $keywordsArray = array_map('trim', explode(',', $record->keywords));
                                    $pureKeywords = array_filter($keywordsArray, fn($kw) => !str_starts_with($kw, 'SDG'));
                                    $component->state(implode(', ', $pureKeywords));
                                }
                            })
                            ->dehydrateStateUsing(function ($state, \Filament\Forms\Get $get) {
                                $sdgs = $get('sdg_selections') ?? [];
                                $pureKeywords = array_filter(array_map('trim', explode(',', $state)));
                                $finalKeywords = array_merge($pureKeywords, $sdgs);
                                return implode(', ', $finalKeywords);
                            }),

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
                        \Filament\Forms\Components\Select::make('authors')
                            ->relationship('authors', 'name')
                            ->multiple()
                            ->searchable(['name', 'email'])
                            ->preload()
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

                        Forms\Components\Fieldset::make('Submitter Info')
                            ->schema([
                                Forms\Components\TextInput::make('submitter_first_name')->disabled(),
                                Forms\Components\TextInput::make('submitter_last_name')->disabled(),
                                Forms\Components\TextInput::make('submitter_email')->disabled(),
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
                
                // 🔥 ADMIN BISA LANGSUNG ON/OFF VERIFIKASI DARI TABEL!
                Tables\Columns\ToggleColumn::make('is_verified')
                    ->label('Verified'),
                    
                Tables\Columns\TextColumn::make('submitter_email')->searchable()->toggleable(),
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