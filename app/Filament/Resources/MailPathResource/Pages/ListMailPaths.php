<?php

namespace App\Filament\Resources\MailPathResource\Pages;

use App\Filament\Resources\MailPathResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMailPaths extends ListRecords
{
    protected static string $resource = MailPathResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

   
}
