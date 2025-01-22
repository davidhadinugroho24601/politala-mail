<?php

namespace App\Filament\Resources\GroupDetailsViewResource\Pages;

use App\Filament\Resources\GroupDetailsViewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGroupDetailsView extends EditRecord
{
    protected static string $resource = GroupDetailsViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
