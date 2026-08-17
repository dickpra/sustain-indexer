<?php

namespace App\Filament\Administrator\Resources\AuthorResource\Pages;

use App\Filament\Administrator\Resources\AuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAuthor extends CreateRecord
{
    protected static string $resource = AuthorResource::class;
}
