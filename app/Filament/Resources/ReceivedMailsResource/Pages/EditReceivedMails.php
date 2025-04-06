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
use App\Services\MailService;
use Illuminate\Support\Facades\App;
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

    public function mount(int|string $record): void
    {
        parent::mount($record);
    
        // Ensure $record is an instance of Mail model
        $mail = $this->getRecord();
    
        $approval = ApprovalChain::where('mail_id', $mail->id)
            ->where('group_id', session('groupID'))
            ->where('status', 'waiting')
            ->where('authority', 'read')
            ->first();
    
        if ($approval) {
        $mailService = App::make(MailService::class);
        $mailService->approveMail($mail);
        }
        // $mailService->approveMail($mail);

        // $mail->update
    }
    
//     public function mount(int|string $record): void
// {
//     parent::mount($record);

//     // Ensure $record is an instance of Mail model
//     $mail = $this->getRecord();

//     // Resolve MailService from the container
//     $mailService = App::make(MailService::class);
//     $mailService->approveMail($mail);
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
