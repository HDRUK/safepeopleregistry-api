<?php

namespace App\Notifications\Affiliations;

use Carbon\Carbon;
use App\Models\Affiliation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

/**
 * Warns a user that their affiliation is coming to its end date.
 */
class AffiliationEndingSoon extends Notification implements ShouldQueue
{
    use Queueable;
    use AffiliationNotification;

    public function __construct(private Affiliation $affiliation)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

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

    public function generateMessage(): string
    {
        $organisationName = $this->affiliation->organisation?->organisation_name;
        $endDate = Carbon::parse($this->affiliation->to)->format('j F Y');

        return "Your affiliation with Organisation {$organisationName} ends on {$endDate}. If this is wrong, please update its end date.";
    }
}
