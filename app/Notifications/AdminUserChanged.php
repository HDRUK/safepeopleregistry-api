<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminUserChanged extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;
    private $details;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $changes)
    {
        $this->message = "$user->first_name $user->last_name details changed.";
        $this->details = $changes;
    }

    /**
     * Specify the delivery channels.
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Store the notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'details' => $this->details,
            'time' => now(),
        ];
    }
}
