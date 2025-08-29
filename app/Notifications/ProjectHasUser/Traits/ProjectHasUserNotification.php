<?php

namespace App\Notifications\ProjectHasUser\Traits;

trait ProjectHasUserNotification
{
    protected $payload;

    protected function buildNotification(string $message, $details)
    {
        $this->payload = [
            'message' => $message,
            'details' => $details ?? [],
            'time' => now(),
        ];
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return $this->payload;
    }
}
