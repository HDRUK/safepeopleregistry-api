<?php

namespace App\Policies;

use App\Models\User;

class AffiliationPolicy
{
    public function isAdmin(User $user): bool
    {
        return $user->isAdmin();
    }
}
