<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrganisationApproved extends Notification
{
    use Queueable;
    private $message;
    private $details;

    /**
     * Create a new notification instance.
     */
    public function __construct($org)
    {
        $this->message = "$org->organisation_name was approved by the admin!";
        $this->details = 'The organization was approved by the admin.';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
}
