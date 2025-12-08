<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustodianAddApprover extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $approver;
    protected $for;

    public function __construct($user, $approver, $for)
    {
        $this->user = $user;
        $this->approver = $approver;
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
                'first_name' => $this->approver->first_name,
                'last_name' => $this->approver->last_name,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'current_custodian':
                return "You added {$this->approver->first_name} {$this->approver->last_name} as an Approver.";

            case 'custodian':
                return "Data Custodian {$this->user->first_name} {$this->user->last_name} added {$this->approver->first_name} {$this->approver->last_name} as an Approver.";

            default:
                break;
        }
    }
}
