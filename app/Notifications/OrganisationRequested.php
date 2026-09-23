<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrganisationRequested extends Notification
{
    use Queueable;

    protected $requester;
    protected $organisationName;
    protected $emailAddress;

    public function __construct($requester, $organisationName, $emailAddress)
    {
        $this->requester = $requester;
        $this->organisationName = $organisationName;
        $this->emailAddress = $emailAddress;
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
                'organisation_name' => $this->organisationName,
                'email_address' => $this->emailAddress,
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        if ($this->emailAddress) {
            return "User " . $this->requester->first_name . " " . $this->requester->last_name . " has requested organisation '" . $this->organisationName . "' be invited to the Safe People Registry. They have provided the email address: " . $this->emailAddress . ". An email has been sent to that email address with instructions on how to contact the system administrator.";
        } else {
            return "User " . $this->requester->first_name . " " . $this->requester->last_name . " has requested organisation '" . $this->organisationName . "' be invited to the Safe People Registry.";
        }
    }
}
