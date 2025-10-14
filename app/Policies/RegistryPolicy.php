<?php

namespace App\Policies;

use App\Models\Registry;
use App\Models\User;

class RegistryPolicy
{
    // note: - use before() for pre-authorisation ($user->isAdmin())
    public function viewAny(User $user): bool
    {
        return $user->inGroup([
            User::GROUP_ADMINS,
            User::GROUP_CUSTODIANS,
            User::GROUP_ORGANISATIONS,
        ]);
    }

    public function view(User $user, Registry $registry): bool
    {
        return $this->viewAny($user) || $user->registry_id === $registry->id;
    }

    public function update(User $user, Registry $registry): bool
    {
        return $user->isAdmin() || $user->registry_id === $registry->id;
    }

    public function delete(User $user, Registry $registry): bool
    {
        //same policy as update, for now
        return $this->update($user, $registry);
    }
}
