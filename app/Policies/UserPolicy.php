<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->inGroup([
            User::GROUP_ADMINS,
            User::GROUP_CUSTODIANS,
            User::GROUP_ORGANISATIONS,
        ]);
    }

    public function view(User $user, User $model): bool
    {
        return $this->viewAny($user) || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // custodians can update themselves
        if (
            $user->user_group === User::GROUP_CUSTODIANS &&
            $model->user_group === User::GROUP_CUSTODIANS &&
            optional($user->custodian_user)->custodian_id === optional($model->custodian_user)->custodian_id
        ) {
            return true;
        }



        // Organisation admins can update themselves
        if (
            $user->user_group === User::GROUP_ORGANISATIONS &&
            $model->user_group === User::GROUP_ORGANISATIONS
        ) {
            // Org admins can update anyone in the same org
            if (!$user->is_delegate) {
                return true;
            }

            // Delegates can only update other delegates
            /** @phpstan-ignore-next-line */
            if ($user->is_delegate && $model->is_delegate) {
                return true;
            }
        }

        // others they can self-update
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function invite(User $user): bool
    {
        return $this->viewAny($user); // same access logic as viewAny for now
    }
}
