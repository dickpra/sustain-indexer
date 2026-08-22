<?php

namespace App\Filament\Administrator\Resources;

use App\Filament\Administrator\Resources\InstitutionResource\Pages;
use App\Models\Institution;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Institution Details')->schema([
                    Forms\Components\TextInput::make('name')->required(),
                    Forms\Components\TextInput::make('country'),
                ])->columns(2),

                Forms\Components\Section::make('Map Location')->schema([
                    // Fitur Cari Lokasi Otomatis
                    \Filament\Forms\Components\TextInput::make('search_location')
                        ->label('Cari Lokasi Otomatis')
                        ->placeholder('Ketik nama kampus, tekan Enter')
                        ->dehydrated(false) 
                        ->suffixAction(
                            \Filament\Forms\Components\Actions\Action::make('search')
                                ->icon('heroicon-m-magnifying-glass')
                                ->action(function (\Filament\Forms\Set $set, $state, $livewire) {
                                    if (blank($state)) return;
                                    $response = \Illuminate\Support\Facades\Http::withHeaders(['User-Agent' => 'SustaIndex/1.0'])
                                        ->get('https://nominatim.openstreetmap.org/search', ['q' => $state, 'format' => 'json', 'limit' => 1]);
                                    $results = $response->json();
                                    if (!empty($results)) {
                                        $set('location', ['lat' => (float)$results[0]['lat'], 'lng' => (float)$results[0]['lon']]);
                                        $set('latitude', (float)$results[0]['lat']);
                                        $set('longitude', (float)$results[0]['lon']);
                                        $livewire->dispatch('refreshMap');
                                    }
                                })
                        ),
                    
                    \Dotswan\MapPicker\Fields\Map::make('location')
                        ->label('Pin on Map')
                        ->defaultLocation(-2.5489, 118.0149) 
                        ->dehydrated(false)
                        ->afterStateHydrated(function ($state, $record, \Filament\Forms\Set $set) {
                            if ($record && $record->latitude && $record->longitude) {
                                $set('location', ['lat' => $record->latitude, 'lng' => $record->longitude]);
                            }
                        })
                        ->afterStateUpdated(function (\Filament\Forms\Set $set, ?array $state): void {
                            if ($state) {
                                $set('latitude', $state['lat']);
                                $set('longitude', $state['lng']);
                            }
                        })->live(),
                        
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('latitude')->numeric()->step('any'),
                        Forms\Components\TextInput::make('longitude')->numeric()->step('any'),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('country')->searchable(),
                Tables\Columns\TextColumn::make('latitude'),
                Tables\Columns\TextColumn::make('longitude'),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstitutions::route('/'),
            'create' => Pages\CreateInstitution::route('/create'),
            'edit' => Pages\EditInstitution::route('/{record}/edit'),
        ];
    }
}