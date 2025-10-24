<?php

namespace App\Policies;

use App\Models\Custodian;
use App\Models\User;

class CustodianPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->inGroup([
            User::GROUP_ADMINS,
            User::GROUP_CUSTODIANS,
            User::GROUP_ORGANISATIONS,
        ]);
    }

    public function view(User $user, Custodian $custodian): bool
    {
        //temp same as viewAny
        return $this->viewAny($user);
    }

    public function viewDetailed(User $user, Custodian $custodian): bool
    {
        return $user->inGroup([
            User::GROUP_ADMINS,
            User::GROUP_CUSTODIANS,
        ]) || optional($user->custodian_user)->custodian_id === $custodian->id;
    }

    public function update(User $user, Custodian $custodian): bool
    {
        return $user->isAdmin() ||
            optional($user->custodian_user)->custodian_id === $custodian->id;
    }

    public function delete(User $user, Custodian $custodian): bool
    {
        return $this->update($user, $custodian);
    }
}
