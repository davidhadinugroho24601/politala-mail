<?php

namespace App\Filament\Resources\SentMailsResource\Pages;

use App\Filament\Resources\SentMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSentMails extends CreateRecord
{
    protected static string $resource = SentMailsResource::class;
   
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Set the writer_id to the authenticated user's ID
        $data['writer_id'] = auth()->id();
        $data['status'] = 'Draft';

// dd($data);
        // Log the data for debugging
        logger('Creating record with data:', $data);

        // Use the model's create method to save the record
        return $this->getModel()::create($data);
    }

}
