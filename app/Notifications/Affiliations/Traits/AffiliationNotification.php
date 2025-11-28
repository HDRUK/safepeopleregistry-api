<?php

namespace App\Notifications\Affiliations\Traits;

use App\Models\Affiliation;
use App\Models\User;

trait AffiliationNotification
{
    protected $payload;

    protected function getAffiliationDetails(Affiliation $affiliation): array
    {
        return [
            'Organisation' => optional($affiliation->organisation)->organisation_name,
            'Start Date' => $affiliation->from,
            'End Date' => $affiliation->to,
            'Relationship' => $affiliation->relationship,
            'Department' => $affiliation->department,
            'Role' => $affiliation->role,
            'Email' => $affiliation->email,
        ];
    }

    // protected function buildNotification(User $user, Affiliation $affiliation, string $initMessage)
    // {
    //     $message = "{$user->first_name} {$user->last_name} $initMessage";
    //     $details = $this->getAffiliationDetails($affiliation);
    //     $this->payload = [
    //         'message' => $message,
    //         'details' => $details,
    //         'time' => now(),
    //     ];
    // }

    // public function via($notifiable)
    // {
    //     return ['database'];
    // }

    // public function toDatabase($notifiable)
    // {
    //     return $this->payload;
    // }
}
