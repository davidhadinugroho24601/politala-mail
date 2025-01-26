<?php

namespace App\Filament\Resources\MailCodesResource\Pages;

use App\Filament\Resources\MailCodesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\MailCodeDetail;

class CreateMailCodes extends CreateRecord
{
    protected static string $resource = MailCodesResource::class;
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {

        $record = $this->getModel()::create($data);
        $value = [
            'code_id' => $record->id
        ];
        for ($i=0; $i < $record->section_qty; $i++) { 
            MailCodeDetail::insert($value);
        }

        return $record;
    }
}
