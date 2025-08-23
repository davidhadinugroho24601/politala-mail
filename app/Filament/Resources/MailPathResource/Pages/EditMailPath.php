<?php

namespace App\Filament\Resources\MailPathResource\Pages;

use App\Filament\Resources\MailPathResource;
use App\Filament\Resources\MailTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMailPath extends EditRecord
{
    protected static string $resource = MailPathResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];

        
    }

    public function getBreadcrumbs(): array
    {
        $record = $this->getRecord();
    
        return [
            MailTemplateResource::getUrl('edit', ['record' => $record->template_id]) => 'Template',
            '#' => 'Alur Surat',
        ];
    }
    
}
