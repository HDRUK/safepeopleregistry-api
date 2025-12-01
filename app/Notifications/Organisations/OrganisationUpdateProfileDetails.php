<?php

namespace App\Notifications\Organisations;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Notification;

class OrganisationUpdateProfileDetails extends Notification
{
    use Queueable;

    private $message;
    private $details;
    private $user;
    private $newOrganisation;
    private $oldOrganisation;
    private $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $newOrganisation, $oldOrganisation, $type)
    {
        $this->newOrganisation = $newOrganisation;
        $this->oldOrganisation = $oldOrganisation;
        $this->user = $user;
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
            'details' => [
                'old' => $this->oldOrganisation,
                'new' => $this->newOrganisation,
                'time' => now(),
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->type) {
            case 'organisation':
                return "{$this->user->first_name} {$this->user->last_name} changed organisation profile.";

            case 'custodian':
                return "Organisation {$this->newOrganisation->organisation_name} has changed their profile.";

            default:
                break;
        }
    }

}