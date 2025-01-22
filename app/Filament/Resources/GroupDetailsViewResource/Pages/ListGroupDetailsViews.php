<?php

namespace App\Filament\Resources\GroupDetailsViewResource\Pages;

use App\Filament\Resources\GroupDetailsViewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGroupDetailsViews extends ListRecords
{
    protected static string $resource = GroupDetailsViewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
