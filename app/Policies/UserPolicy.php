<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array(
            $user->user_group,
            [
                User::GROUP_ADMINS,
                User::GROUP_CUSTODIANS,
                User::GROUP_ORGANISATIONS
            ]
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
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
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->user_group === User::GROUP_ADMINS;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admins can update anyone
        if ($user->user_group === User::GROUP_ADMINS) {
            return true;
        }

        // Custodians can update other custodians with the same custodian_id
        if (
            $user->user_group === User::GROUP_CUSTODIANS &&
            $model->user_group === User::GROUP_CUSTODIANS &&
            optional($user->custodian_user)->custodian_id === optional($model->custodian_user)->custodian_id
        ) {
            return true;
        }

        // Organisations can update other users in the same organisation
        if (
            $user->user_group === User::GROUP_ORGANISATIONS &&
            $model->user_group === User::GROUP_ORGANISATIONS &&
            $user->organisation_id === $model->organisation_id
        ) {
            if ((int) $user->is_delegate === 0) {
                return true;
            }

            if ((int) $user->is_delegate === 1 && (int) $model->is_delegate === 1) {
                return true;
            }
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->user_group === User::GROUP_ADMINS;
    }
}
