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

    public function viewMembersOnly(User $user, Custodian $custodian): bool
    {
        return $user->isAdmin() || $this->isMember($user, $custodian);
    }

    public function update(User $user, Custodian $custodian): bool
    {
        return $user->isAdmin()
            || ($this->isMember($user, $custodian) && $user->custodian_user?->isReadOnly() !== true);
    }

    public function delete(User $user, Custodian $custodian): bool
    {
        return $this->update($user, $custodian);
    }

    private function isMember(User $user, Custodian $custodian): bool
    {
        return optional($user->custodian_user)->custodian_id === $custodian->id;
    }
}
