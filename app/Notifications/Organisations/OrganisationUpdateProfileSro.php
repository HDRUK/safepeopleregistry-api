<?php

namespace App\Notifications\Organisations;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrganisationUpdateProfileSro extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $organisation;
    protected $changes;
    protected $for;

    public function __construct($user, $organisation, $changes, $for)
    {
        $this->user = $user;
        $this->organisation = $organisation;
        $this->changes = $changes;
        $this->for = $for;
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
            'details' => $this->changes,
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        switch ($this->for) {
            case 'organisation':
                return "{$this->user->first_name} {$this->user->last_name} has changed the SRO profile.";

            case 'custodian':
                return "Organisation {$this->organisation->organisation_name} has changed the SRO profile.";

            default:
                break;
        }

    }
}
