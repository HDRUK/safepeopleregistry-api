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
            'id' => $affiliation->id,
            'organisation' => optional($affiliation->organisation)->name,
            'relationship' => $affiliation->relationship,
            'department' => $affiliation->department,
            'role' => $affiliation->role,
            'from' => $affiliation->from,
            'to' => $affiliation->to,
            'email' => $affiliation->email,
            'ror' => $affiliation->ror,
            'registry_id' => $affiliation->registry_id,
        ];
    }

    protected function buildNotification(User $user, Affiliation $affiliation, string $initMessage): array
    {
        $message = "{$user->first_name} {$user->last_name} $initMessage";
        $details = $this->getAffiliationDetails($affiliation);
        $this->payload = [
            'message' => $message,
            'details' => $details,
            'time' => now(),
        ];
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return $this->payload;
    }
}
