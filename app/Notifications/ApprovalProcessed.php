<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;

class ApprovalProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    public $status;
    public $record;

    public function __construct(string $status, $record)
    {
        $this->status = $status;
        $this->record = $record;
    }

    public function via($notifiable)
    {
        return ['mail']; // only email channel
    }

    public function toMail($notifiable)
    {
//

// if (isset($error_msg)) {
//  echo $error_msg;
// }
// echo $response;
        // // Send WhatsApp via Fonnte API
        // Http::withHeaders([
        //     'Authorization' => env('FONNTE_TOKEN'),
        // ])->post('https://api.fonnte.com/send', [
        //     'target'  => $notifiable->phone ?? '62895704037087', // fallback number
        //     'message' => "Your mail with subject '{$this->record->subject}' has been {$this->status}.",
        // ]);

        // Return the email as usual
        return (new MailMessage)
            ->subject('Approval Status: ' . $this->status)
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your mail with subject "' . $this->record->subject . '" has been ' . $this->status . '.')
            ->action('View Mail', url('/admin/received-mails/' . $this->record->id . '/edit'))
            ->line('Thank you for using our application!');
    }
}
