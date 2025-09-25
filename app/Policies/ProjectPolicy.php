<?php

namespace App\Policies;

class ProjectPolicy
{
    public function viewProjectUserDetails(User $user): bool
    {
        return $user->inGroup([
            User::GROUP_ADMINS,
            User::GROUP_CUSTODIANS
        ]);
    }
}
