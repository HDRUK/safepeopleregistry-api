<?php

namespace App\Notifications\Affiliations;

use App\Models\Affiliation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

class AffiliationChanged extends Notification
{
    use Queueable;
    use AffiliationNotification;

    public function __construct(User $user, Affiliation $oldAffiliation, Affiliation $newAffiliation, $type)
    {
        $oldDetails = $this->getAffiliationDetails($oldAffiliation);
        $newDetails = $this->getAffiliationDetails($newAffiliation);
        $changes = [];
        foreach ($oldDetails as $key => $oldValue) {
            $newValue = $newDetails[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        $this->payload = [
            'message' => $this->buildMessage($user, $type),
            'details' => $changes,
            'time' => now(),
        ];
    }

    public function buildMessage($user, $type)
    {
        switch ($type) {
            case 'user':
                return "You changed your affiliation.";

            case 'organisation':
                return "Person {$user->first_name} {$user->last_name} has changed their affiliation.";

            case 'custodian':
                return "Person {$user->first_name} {$user->last_name} has changed their affiliation.";

            default:
                break;
        }
    }
}
