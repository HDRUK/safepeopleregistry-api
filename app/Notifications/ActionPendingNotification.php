<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ActionLog;

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
            'actions' => [
                'profile_completed' => !ActionLog::where('user_id', $notifiable->id)->where('action', 'profile_completed')->exists(),
                'affiliations_updated' => !ActionLog::where('user_id', $notifiable->id)->where('action', 'affiliations_updated')->exists(),
            ]
        ];
    }


}
