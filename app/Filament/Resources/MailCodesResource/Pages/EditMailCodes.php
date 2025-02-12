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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['status'] === 'enabled') {
            // Disable all other rows before enabling the selected one
            \App\Models\MailCode::where('id', '!=', $this->record->id)
                ->update(['status' => 'disabled']);
        }
    
        return $data;
    }
    
}
