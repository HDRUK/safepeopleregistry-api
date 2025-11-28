<?php

namespace App\Notifications\Affiliations;

use App\Models\Affiliation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AffiliationCreated extends Notification
{
    use Queueable;

    private $user;
    private $affiliation;
    private $type;
    private $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, Affiliation $affiliation, $type)
    {
        $this->user = $user;
        $this->affiliation = $affiliation;
        $this->type = $type;
        $this->message = $this->generateMessage($affiliation);
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
            'details' => $this->getAffiliationDetails(),
            'time' => now(),
        ];
    }

    protected function getAffiliationDetails(): array
    {
        return [
            'Organisation' => optional($this->affiliation->organisation)->organisation_name,
            'Start Date' => $this->affiliation->from,
            'End Date' => $this->affiliation->to,
            'Relationship' => $this->affiliation->relationship,
            'Department' => $this->affiliation->department,
            'Role' => $this->affiliation->role,
            'Email' => $this->affiliation->email,
        ];
    }

    public function generateMessage()
    {
        switch ($this->type) {
            case 'user':
                return "You send an affiliation request to Organisation {$this->affiliation->organisation->organisation_name}.";

            case 'organisation':
                return "You have been sent an affiliation request from Person {$this->user->first_name} {$this->user->last_name}. [Button: Go to User profile]";

            case 'custodian':
                return "Person {$this->user->first_name} {$this->user->last_name} sent an affiliation request to Organisation {$this->affiliation->organisation->organisation_name}.";

            default:
                break;
        }
    }

}
