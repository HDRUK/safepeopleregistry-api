<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActionPendingNotification extends Notification
{
    use Queueable;

    protected ?array $actions = null;

    /**
     * Create a new notification instance.
     */
    public function __construct($actions)
    {
        $this->actions = $actions;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'You have incomplete profile actions',
            'actions' => $this->actions
        ];
    }


}
