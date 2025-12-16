<?php

namespace App\Notifications\Affiliations;

use App\Models\Affiliation;
use Illuminate\Notifications\Notification;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

class AffiliationCreated extends Notification
{
    use AffiliationNotification;

    private $user;
    private $affiliation;
    private $for;
    private $affiliationRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, Affiliation $affiliation, $for, $affiliationRequest = false)
    {
        $this->user = $user;
        $this->affiliation = $affiliation;
        $this->for = $for;
        $this->affiliationRequest = $affiliationRequest;
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
                'new' => $this->getAffiliationDetails($this->affiliation),
                'time' => now(),
            ],
            'time' => now(),
        ];
    }

    public function generateMessage()
    {
        if ($this->affiliationRequest) {
            switch ($this->for) {
                case 'user':
                    return "You send an affiliation request to Organisation {$this->affiliation->organisation->organisation_name}.";

                case 'organisation':
                    return "You have been sent an affiliation request from Person {$this->user->first_name} {$this->user->last_name}. [Button: Go to User profile]";

                case 'custodian':
                    return "Person {$this->user->first_name} {$this->user->last_name} sent an affiliation request to Organisation {$this->affiliation->organisation->organisation_name}.";

                default:
                    break;
            }
        } else {
            switch ($this->for) {
                case 'user':
                    return "You created a new affiliation.";

                case 'organisation':
                    return "Person {$this->user->first_name} {$this->user->last_name} created a new affiliation.";

                case 'custodian':
                    return "Person {$this->user->first_name} {$this->user->last_name} created a new affiliation.";

                default:
                    break;
            }

        }
    }

}
