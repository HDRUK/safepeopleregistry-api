<?php

namespace App\Notifications\Affiliations;

use App\Models\Affiliation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Affiliations\Traits\AffiliationNotification;


class AffiliationChanged extends Notification
{
    use Queueable, AffiliationNotification;

    public function __construct(User $user, Affiliation $oldAffiliation, Affiliation $newAffiliation)
    {
        $oldDetails = $this->getAffiliationDetails($oldAffiliation);
        $newDetails = $this->getAffiliationDetails($newAffiliation);
        $changes = [];
        foreach ($oldDetails as $key => $oldValue) {
            $newValue = $newDetails[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[] = [
                    'key' => $key,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        $this->payload = [
            'message' => "{$user->first_name} {$user->last_name} affiliation details have been changed",
            'details' => $changes,
            'time' => now(),
        ];
    }
}
