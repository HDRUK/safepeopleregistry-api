<?php

namespace App\Traits;

use App\Models\Affiliation;
use TriggerEmail;

trait AffiliationManager
{
    public function sendEmailVerificationAffiliation(Affiliation $affiliation): void
    {
        $email = [
            'type' => 'AFFILIATION_VERIFY',
            'to' => $affiliation->id,
            'by' => $affiliation->id,
            'for' => $affiliation->id,
            'identifier' => 'affiliation_user_professional_email_confirm',
        ];

        TriggerEmail::spawnEmail($email);
    }
}
