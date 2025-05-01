<?php

namespace App\Policies;

use App\Models\Registry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RegistryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
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

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Registry $registry): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Registry $registry): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Registry $registry): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Registry $registry): bool
    {
        //
    }
}
