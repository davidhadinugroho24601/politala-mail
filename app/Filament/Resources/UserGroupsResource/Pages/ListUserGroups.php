<?php

namespace App\Filament\Resources\UserGroupsResource\Pages;

use App\Filament\Resources\UserGroupsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserGroups extends ListRecords
{
    protected static string $resource = UserGroupsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
