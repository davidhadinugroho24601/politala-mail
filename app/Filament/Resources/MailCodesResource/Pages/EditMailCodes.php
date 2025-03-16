<?php

namespace App\Filament\Resources\MailCodesResource\Pages;

use App\Filament\Resources\MailCodesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\MailCodeDetail;

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
    
        $mailCodeId = $this->record->id; // Fixed undefined $record
        $mergedText = MailCodeDetail::where('code_id', $mailCodeId)
            ->pluck('text')
            ->map(fn($text) => trim(preg_replace('/\s+/', ' ', $text))) // Normalize whitespace
            ->implode('/');
        $data['code'] = $mergedText; // Assign to $data['code'] instead of $this->record->code
        // dd($data['code']);

         
        return $data;
    }
    
}
