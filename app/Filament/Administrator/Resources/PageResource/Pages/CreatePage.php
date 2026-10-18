<?php

namespace App\Filament\Administrator\Resources\PageResource\Pages;

use App\Filament\Administrator\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
}
