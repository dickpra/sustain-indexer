<?php

namespace App\Filament\Administrator\Resources\AuthorResource\Pages;

use App\Filament\Administrator\Resources\AuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuthors extends ListRecords
{
    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
