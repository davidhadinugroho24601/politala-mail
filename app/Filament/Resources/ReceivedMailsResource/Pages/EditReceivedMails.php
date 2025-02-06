<?php

namespace App\Filament\Resources\ReceivedMailsResource\Pages;

use App\Filament\Resources\ReceivedMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReceivedMails extends EditRecord
{
    protected static string $resource = ReceivedMailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
