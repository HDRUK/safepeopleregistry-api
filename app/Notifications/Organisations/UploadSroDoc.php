<?php

namespace App\Notifications\Organisations;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UploadSroDoc extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;
    private $details;

    public function __construct($user, $files)
    {
        $this->message = "$user->first_name $user->last_name uploaded the SRO declaration";
        $this->details = $files;
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
