<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;

class ActionPendingNotification extends Notification
{
    use Queueable;

    protected mixed $actions;
    protected string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($for, $actions)
    {
        $this->message = $this->generateMessage($for);
        $this->actions = $actions;
    }

    private function generateMessage($for): string
    {
        return match ($for) {
            User::GROUP_USERS           => 'Your profile is incomplete.',
            User::GROUP_ORGANISATIONS   => 'Your organisation\'s profile is incomplete.',
            User::GROUP_CUSTODIANS      => 'Your custodian\'s profile is incomplete.',
            default                     => 'You have pending actions.',
        };
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'actions' => $this->actions
        ];
    }


}
