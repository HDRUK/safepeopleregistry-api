<?php

namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;

class NotificationObserver
{
    public function creating(DatabaseNotification $notification): void
    {
        if (isset($notification->data['causer_id'])) {
            $notification->causer_id = $notification->data['causer_id'];
        }

        if (isset($notification->data['causer_action'])) {
            $notification->causer_action = $notification->data['causer_action'];
        }
    }
}
