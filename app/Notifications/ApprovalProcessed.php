<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalProcessed extends Notification
{
    use Queueable;

    public $status;
    public $record;

    public function __construct($status, $record)
    {
        $this->status = $status;
        $this->record = $record;
    }

    public function via($notifiable)
    {
        return ['mail']; // Send via email
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Approval Status: ' . $this->status)
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your mail with subject "' . $this->record->subject . '" has been ' . $this->status . '.')
            ->action('View Mail', url('/mails/' . $this->record->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
