<?php

namespace App\Filament\Resources\ReceivedMailsResource\Pages;

use App\Filament\Resources\ReceivedMailsResource;
use App\Models\ApprovalChain;
use App\Models\User;
use App\Notifications\ApprovalProcessed;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Session;

class ApproveMail extends EditRecord
{
    protected static string $resource = ReceivedMailsResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $groupID = Session::get('groupID');

        $currentApproval = ApprovalChain::where('mail_id', $this->record->id)
            ->where('group_id', $groupID)
            ->orderBy('id')
            ->first();

        if (!$currentApproval) {
            Notification::make()->title('No approval step found.')->danger()->send();
            return $data;
        }

        $nextApproval = ApprovalChain::where('mail_id', $this->record->id)
            ->where('id', '>', $currentApproval->id)
            ->orderBy('id')
            ->first();

        $currentApproval->update(['status' => 'approved']);

        if ($nextApproval) {
            $this->record->update(['target_id' => $nextApproval->group_id]);
        } else {
            ApprovalChain::where('mail_id', $this->record->id)
                ->where('group_id', $groupID)
                ->update(['status' => 'finished']);
        }

        $groupUsers = User::whereIn('id', function ($query) {
            $query->select('user_id')
                ->from('group_details')
                ->where('group_id', $this->record->group_id);
        })->get();

        foreach ($groupUsers as $user) {
            $user->notify(new ApprovalProcessed('Approved', $this->record));
        }

        Notification::make()
            ->title('Approval processed successfully!')
            ->success()
            ->send();

        return $data;
    }
}
