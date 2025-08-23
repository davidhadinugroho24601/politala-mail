<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class FonnteChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toFonnte')) {
            return;
        }

        $data = $notification->toFonnte($notifiable);

        if (! $data || empty($data['target']) || empty($data['message'])) {
            return;
        }

        // Send request to Fonnte API
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.fonnte.com/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'target'  => $data['target'],
                'message' => $data['message'],
            ],
            CURLOPT_HTTPHEADER => [
                "Authorization: " . env('FONNTE_TOKEN'),
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
