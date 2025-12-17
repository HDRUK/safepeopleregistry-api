<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustodianUserStatusUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;
    private $details;
    private $for;

    /**
     * Create a new notification instance.
     */
    public function __construct($changes, $for)
    {
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
        $state = $this->details['new_state'];

        $oldState = convertStates($this->details['old_state']);
        $newState = convertStates($this->details['new_state']);
        $custodianName = $this->details['custodian_name'];
        $userName = $this->details['user_name'];
        $projectTitle = $this->details['project_title'];

        switch ($this->for) {
            case 'user':
                return "Data Custodian {$custodianName} changed your validation status, for project {$projectTitle}, from {$oldState} to {$newState}.";

            case 'organisation':
                return "Data Custodian {$custodianName} changed the validation status of user {$userName}, for project {$projectTitle}, from {$oldState} to {$newState}.";

            case 'current_custodian':
                return "You changed the validation status of user {$userName}, for project {$projectTitle}, from {$oldState} to {$newState}.";

            case 'custodian':
                return "Data Custodian {$custodianName} changed the validation status of user {$userName}, for project {$projectTitle}, from {$oldState} to {$newState}.";

            default:
                break;
        }
    }
}
