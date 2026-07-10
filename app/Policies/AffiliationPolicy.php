<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Affiliation;

class AffiliationPolicy
{
    public function userAffiliations(User $user)
    {

        if ($user->isAdmin()) {
            return true;
        }

        return Affiliation::where([
                'registry_id' => $user->registry_id
            ])->exists();
    }

}
