<?php

namespace App\Policies;

use App\Models\Custodian;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustodianPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return  in_array(
            $user->user_group,
            [
                User::GROUP_ADMINS,
                User::GROUP_CUSTODIANS,
                User::GROUP_ORGANISATIONS,
            ]
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Custodian $custodian): bool
    {
        return  in_array(
            $user->user_group,
            [
                User::GROUP_ADMINS,
                User::GROUP_CUSTODIANS,
                User::GROUP_ORGANISATIONS,
            ]
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return  in_array(
            $user->user_group,
            [User::GROUP_ADMINS]
        );
    }

    public function view_detailed(User $user, Custodian $custodian): bool
    {
        if (in_array(
            $user->user_group,
            [User::GROUP_ADMINS, User::GROUP_CUSTODIANS]
        )) {
            return true;
        }
        return optional($user->custodian_user)->custodian_id === $custodian->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Custodian $custodian): bool
    {
        if ($user->user_group === User::GROUP_ADMINS) {
            return true;
        }

        return optional($user->custodian_user)->custodian_id === $custodian->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Custodian $custodian): bool
    {
        if ($user->user_group === User::GROUP_ADMINS) {
            return true;
        }

        return optional($user->custodian_user)->custodian_id === $custodian->id;
    }
}
