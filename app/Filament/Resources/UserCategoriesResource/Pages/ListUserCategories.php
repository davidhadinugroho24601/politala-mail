<?php

namespace App\Filament\Resources\UserCategoriesResource\Pages;

use App\Filament\Resources\UserCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserCategories extends ListRecords
{
    protected static string $resource = UserCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
