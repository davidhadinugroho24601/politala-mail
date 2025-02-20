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
class EditReceivedMails extends EditRecord
{
    protected static string $resource = ReceivedMailsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($this->record->id);
    }
    //     $finishedChain = ApprovalChain::where('mail_id', $data['id'])
    //     ->where('group_id', $groupID)->where('status', 'finished');
    //     if ($finishedChain) {
    //         // Get the template ID where the template name is 'Disposisi'
    //         $templateId = MailTemplate::where('name', 'Disposisi')->value('id');
        
    //         $groupId = Group::whereNull('parent_id')->value('id');
    //         // Get the first user who doesn't have a parent_id (writer)
    //         $writerId = GroupDetailsView::where('group_id',$groupId)->first()->value('user_id');
        

    //         // Get the target_id from the mail
    //         $targetId = $data['final_id']; // Authenticated user
        
    //         if ($data['template_id'] == $templateId && $writerId && $targetId) {
    //             // Create the mail entry
    //             $mail = Mail::create([
    //                 // 'template_id' => '',
    //                 'group_id' => $groupId,
    //                 'subject' => 'Surat Tugas',
    //                 'writer_id' => $writerId,
    //                 'final_id' => $targetId,
    //                 'target_id' => $targetId, // Store target_id in the Mail
    //             ]);
        
    //             // Create an ApprovalChain for the newly created mail
    //             ApprovalChain::create([
    //                 'mail_id' => $mail->id,
    //                 'target_id' => $mail->target_id,
    //                 'status' => 'waiting',
    //             ]);
        
    //             // Send a Filament notification
    //             Notification::make()
    //                 ->title('New Mail Created')
    //                 ->success()
    //                 ->body('A new mail has been created and assigned for approval.')
    //                 ->send();
    //         }
    //     }
    // }
}
