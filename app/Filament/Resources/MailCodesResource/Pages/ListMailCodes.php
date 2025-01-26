<?php

namespace App\Filament\Resources\MailCodesResource\Pages;

use App\Filament\Resources\MailCodesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMailCodes extends ListRecords
{
    protected static string $resource = MailCodesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
