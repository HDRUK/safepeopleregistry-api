<?php

namespace App\Notifications\Affiliations;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Affiliation;
use App\Enums\AffiliationEndDateSource;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

/**
 * Tells the other side of an affiliation that its end date has been recorded, changed or removed.
 */
class AffiliationEndDateRecorded extends Notification implements ShouldQueue
{
    use Queueable;
    use AffiliationNotification;

    public function __construct(
        private User $user,
        private Affiliation $affiliation,
        private AffiliationEndDateSource $recordedBy
    ) {
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
        $userName = "{$this->user->first_name} {$this->user->last_name}";
        $endDate = $this->affiliation->to ? Carbon::parse($this->affiliation->to)->format('j F Y') : null;

        // Sent to the side that did not record the end date.
        if ($this->recordedBy === AffiliationEndDateSource::Organisation) {
            return $endDate
                ? "Organisation {$organisationName} recorded the end date of your affiliation as {$endDate}."
                : "Organisation {$organisationName} removed the end date of your affiliation.";
        }

        return $endDate
            ? "Person {$userName} recorded the end date of their affiliation with {$organisationName} as {$endDate}."
            : "Person {$userName} removed the end date of their affiliation with {$organisationName}.";
    }
}
