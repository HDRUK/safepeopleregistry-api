<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProfileDetails extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;
    private $message;
    private $details;
    private $for;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $changes, $for)
    {
        $this->user = $user;
        $this->details = $changes;
        $this->for = $for;
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
        switch ($this->for) {
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
