<?php

namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;

class NotificationObserver
{
    public function creating(DatabaseNotification $notification): void
    {
        if (isset($notification->data['causer_id'])) {
            $notification->causer_id = $notification->data['causer_id'];

            // Optionally remove it from the data array to avoid duplication
            $data = $notification->data;
            // disabled temp
            // unset($data['causer_id']);
            $notification->data = $data;
        }

        // You can do the same for other custom fields
        if (isset($notification->data['causer_action'])) {
            $notification->causer_action = $notification->data['causer_action'];
            // disabled temp
            // unset($notification->data['causer_action']);
            $notification->data = $notification->data;
        }
    }
}
