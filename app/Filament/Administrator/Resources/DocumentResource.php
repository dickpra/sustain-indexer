<?php

namespace App\Filament\Administrator\Resources;

use App\Filament\Administrator\Resources\DocumentResource\Pages;
use App\Filament\Administrator\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('document_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('journal_title')
                    ->maxLength(255),
                Forms\Components\TextInput::make('publisher')
                    ->maxLength(255),
                Forms\Components\Textarea::make('abstract')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('keywords')
                    ->maxLength(255),
                Forms\Components\TextInput::make('document_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pub_year')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pages')
                    ->numeric(),
                Forms\Components\TextInput::make('reference_count')
                    ->numeric(),
                Forms\Components\Toggle::make('is_peer_reviewed')
                    ->required(),
                Forms\Components\TextInput::make('submitter_first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('submitter_last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('submitter_email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('doi')
                    ->maxLength(255),
                Forms\Components\TextInput::make('citation_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_verified')
                    ->required(),
                Forms\Components\TextInput::make('views')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('journal_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('publisher')
                    ->searchable(),
                Tables\Columns\TextColumn::make('keywords')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pub_year')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pages')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_peer_reviewed')
                    ->boolean(),
                Tables\Columns\TextColumn::make('submitter_first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('submitter_last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('submitter_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('doi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('citation_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
        return [
            //
        ];
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
