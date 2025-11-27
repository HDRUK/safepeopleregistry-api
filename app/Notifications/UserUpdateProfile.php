<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserUpdateProfile extends Notification
{
    use Queueable;

    private $user;
    private $message;
    private $details;
    private $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $changes, $type)
    {
        $this->user = $user;
        $this->details = $changes;
        $this->type = $type;
        $this->message = $this->generateMessage();
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

    public function generateMessage()
    {
        switch ($this->type) {
            case 'user':
                return "You changed your profile.";

            case 'organisation':
                return "Person {$this->user->first_name} {$this->user->last_name} has changed their profile.";

            case 'custodian':
                return "Person {$this->user->first_name} {$this->user->last_name} has changed their profile.";

            default:
                break;
        }
    }
}