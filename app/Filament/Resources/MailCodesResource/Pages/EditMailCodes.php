<?php

namespace App\Filament\Resources\MailCodesResource\Pages;

use App\Filament\Resources\MailCodesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMailCodes extends EditRecord
{
    protected static string $resource = MailCodesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
