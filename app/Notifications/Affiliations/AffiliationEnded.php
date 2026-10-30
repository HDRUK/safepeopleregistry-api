<?php

namespace App\Notifications\Affiliations;

use Carbon\Carbon;
use App\Models\User;
use App\Models\State;
use App\Models\Project;
use App\Models\Affiliation;
use App\Enums\NotificationAudience;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Affiliations\Traits\AffiliationNotification;

/**
 * Tells the user, their Organisation and their projects' Custodians that an affiliation has ended.
 * Custodians are told per project, as they must revoke the user's data access for each.
 */
class AffiliationEnded extends Notification implements ShouldQueue
{
    use Queueable;
    use AffiliationNotification;

    public function __construct(
        private User $user,
        private Affiliation $affiliation,
        private NotificationAudience $for,
        private ?Project $project = null
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
        $endDate = Carbon::parse($this->affiliation->to)->format('j F Y');
        $neverVerified = $this->affiliation->getState() === State::STATE_AFFILIATION_NEVER_VERIFIED;

        return match ($this->for) {
            NotificationAudience::User => $neverVerified
                ? "Your affiliation with Organisation {$organisationName} ended on {$endDate} without your professional email being verified."
                : "Your affiliation with Organisation {$organisationName} ended on {$endDate}.",
            NotificationAudience::Organisation => "Person {$userName} left Organisation {$organisationName} on {$endDate}.",
            NotificationAudience::Custodian => "Person {$userName} left Organisation {$organisationName} on {$endDate}, so has left project {$this->project?->title}. You must revoke any data access they still have, then confirm this in the Safe People Registry.",
        };
    }
}
