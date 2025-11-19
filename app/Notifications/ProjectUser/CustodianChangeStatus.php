<?php

namespace App\Notifications\ProjectUser;

use App\Models\State;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustodianChangeStatus extends Notification
{
    use Queueable;

    private $message;
    private $details;
    private $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $changes, $type)
    {
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
        $state = $this->details['new_state'];

        $oldState = convertStates($this->details['old_state']);
        $newState = convertStates($this->details['new_state']);

        if ($state === State::STATE_MORE_USER_INFO_REQ) {
            switch ($this->type) {
                case 'user':
                    return "Data Custodian {$this->details['custodian_name']} changed your validation status, for project {$this->details['project_title']}, from {$oldState} to {$newState}.";

                case 'organisation':
                    return "Data Custodian {$this->details['custodian_name']} changed the validation status of user {$this->details['user_name']}, for project {$this->details['project_title']}, from {$oldState} to {$newState}.";

                case 'custodian':
                    return "You changed the validation status of user {$this->details['user_name']}, for project {$this->details['project_title']}, from {$oldState} to {$newState}.";

                default:
                    break;
            }

        }
    }
}
