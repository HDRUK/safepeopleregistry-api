<?php

namespace App\Notifications\Affiliations;

use App\Models\Affiliation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

class AffiliationCreated extends Notification
{
    use Queueable;
    use AffiliationNotification;

    public function __construct(User $user, Affiliation $affiliation)
    {
        $this->buildNotification($user, $affiliation, " created a new affiliation!");
    }
}
