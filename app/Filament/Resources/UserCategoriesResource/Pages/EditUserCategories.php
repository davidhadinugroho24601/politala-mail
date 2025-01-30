<?php

namespace App\Filament\Resources\UserCategoriesResource\Pages;

use App\Filament\Resources\UserCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserCategories extends EditRecord
{
    protected static string $resource = UserCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
