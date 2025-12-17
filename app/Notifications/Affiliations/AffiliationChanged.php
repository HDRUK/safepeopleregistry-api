<?php

namespace App\Notifications\Affiliations;

use App\Models\User;
use App\Models\Affiliation;
use Illuminate\Notifications\Notification;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

class AffiliationChanged extends Notification
{
    use AffiliationNotification;

    private $user;
    private $oldAffiliation;
    private $newAffiliation;
    private $for;
    private $affiliationRequest;

    public function __construct(User $user, Affiliation $oldAffiliation, Affiliation $newAffiliation, $for, $affiliationRequest = false)
    {
        $this->user = $user;
        $this->oldAffiliation = $oldAffiliation;
        $this->newAffiliation = $newAffiliation;
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
            'message' => $this->buildMessage(),
            'action_url' => $this->getUrl(),
            'details' => [
                'old' => $this->getAffiliationDetails($this->oldAffiliation),
                'new' => $this->getAffiliationDetails($this->newAffiliation),
            ],
            'time' => now(),
        ];
    }

    public function buildMessage()
    {
        if ($this->affiliationRequest) {
            switch ($this->for) {
                case 'user':
                    return "You send an affiliation request to Organisation {$this->newAffiliation->organisation->organisation_name}.";

                case 'organisation':
                    return "You have been sent an affiliation request from Person {$this->user->first_name} {$this->user->last_name}.";

                case 'custodian':
                    return "Person {$this->user->first_name} {$this->user->last_name} sent an affiliation request to Organisation {$this->newAffiliation->organisation->organisation_name}.";

                default:
                    break;
            }
        } else {
            switch ($this->for) {
                case 'user':
                    return "You updated your affiliation.";

                case 'organisation':
                    return "Person {$this->user->first_name} {$this->user->last_name} has updated their affiliation.";

                case 'custodian':
                    return "Person {$this->user->first_name} {$this->user->last_name} has updated their affiliation.";

                default:
                    break;
            }
        }

    }

    public function getUrl()
    {
        $url = config('speedi.system.portal_url') . '/en/organisation/profile/user-administration/employees-and-students/' . $this->user->id . '/affiliations';
        if ($this->affiliationRequest && $this->for === 'organisation') {
            return $url;
        }

        return null;
    }
}
