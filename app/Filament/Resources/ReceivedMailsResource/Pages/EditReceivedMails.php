<?php

namespace App\Filament\Resources\ReceivedMailsResource\Pages;

use App\Filament\Resources\ReceivedMailsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\models\ApprovalChain;
use App\models\MailTemplate;
use App\models\Group;
use App\models\GroupDetailsView;
use App\models\Mail;
use Filament\Actions\Action;
class EditReceivedMails extends EditRecord
{
    protected static string $resource = ReceivedMailsResource::class;

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->hidden();
    }    

    protected function getCancelFormAction(): \Filament\Actions\Action
    {
        return parent::getCancelFormAction()
        ->hidden();

    } 

    // protected function getDeleteFormAction(): \Filament\Actions\Action
    // {
    //     return parent::getDeleteFormAction()
    //     ->hidden();

    // } 
    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
            ->label('Kembali')
            ->url($this->getResource()::getUrl('index'))
            ->color('warning'),
        ];
    }
    

   
}
