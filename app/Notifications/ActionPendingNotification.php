<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ActionLog;
use App\Enums\ActionLogType;

class ActionPendingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'You have incomplete profile actions',
            'actions' => ActionLog::where('entity_type', ActionLogType::USER)
                            ->where('entity_id', $notifiable->id)
                            ->get()
        ];
    }


}
