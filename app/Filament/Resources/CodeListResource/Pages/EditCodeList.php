<?php

namespace App\Filament\Resources\CodeListResource\Pages;

use App\Filament\Resources\CodeListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCodeList extends EditRecord
{
    protected static string $resource = CodeListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
