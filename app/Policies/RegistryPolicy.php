<?php

namespace App\Policies;

use App\Models\Registry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegistryPolicy
{

    public function viewAll(User $user): bool
    {
        if (in_array(
            $user->user_group,
            [
                User::GROUP_ADMINS,
                User::GROUP_CUSTODIANS,
                User::GROUP_ORGANISATIONS
            ]
        )) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Registry $registry): bool
    {
        if (in_array(
            $user->user_group,
            [
                User::GROUP_ADMINS,
                User::GROUP_CUSTODIANS,
                User::GROUP_ORGANISATIONS
            ]
        )) {
            return true;
        }
        return $user->registry_id === $registry->id;
    }

    public function create(User $user): bool
    {
        return $user->user_group ===  User::GROUP_ADMINS;
    }

    public function update(User $user, Registry $registry): bool
    {
        return $user->user_group ===  User::GROUP_ADMINS || $user->registry_id === $registry->id;
    }
}
