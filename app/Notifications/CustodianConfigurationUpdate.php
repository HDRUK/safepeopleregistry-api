<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustodianConfigurationUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $for;

    public function __construct($user, $for)
    {
        $this->user = $user;
        $this->for = $for;
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
            'message' => $this->generateMessage(),
            'details' => [
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'current_custodian':
                return "You have updated the configuration.";

            case 'custodian':
                return "{$this->user->first_name} {$this->user->last_name} from the data custodian updated the configuration.";

            default:
                break;
        }
    }
}
