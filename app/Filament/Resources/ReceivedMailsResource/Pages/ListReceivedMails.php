<?php

namespace App\Filament\Resources\ReceivedMailsResource\Pages;

use App\Filament\Resources\ReceivedMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReceivedMails extends ListRecords
{
    protected static string $resource = ReceivedMailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
