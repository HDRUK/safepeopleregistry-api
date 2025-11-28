<?php

namespace App\Notifications\Affiliations;

use App\Models\User;
use App\Models\Affiliation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

class AffiliationChanged extends Notification
{
    use Queueable;

    use AffiliationNotification;

    private $user;
    private $oldAffiliation;
    private $newAffiliation;
    private $type;

    public function __construct(User $user, Affiliation $oldAffiliation, Affiliation $newAffiliation, $type)
    {
        $this->user = $user;
        $this->oldAffiliation = $oldAffiliation;
        $this->newAffiliation = $newAffiliation;
        $this->type = $type;
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
            'details' => [
                'old' => $this->getAffiliationDetails($this->oldAffiliation),
                'new' => $this->getAffiliationDetails($this->newAffiliation),
                'time' => now(),
            ],
            'time' => now(),
        ];
    }

    public function buildMessage()
    {
        switch ($this->type) {
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
