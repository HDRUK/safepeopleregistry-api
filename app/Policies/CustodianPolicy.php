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

    /**
     * Read access to data that belongs to this Custodian and is only for its own users
     * (e.g. the access revocations list): admins, or any user of this Custodian,
     * including CUSTODIAN_READ_ONLY users.
     *
     * Unlike viewDetailed, which admits every CUSTODIANS-group user from any Custodian,
     * this is limited to the Custodian's own users. Unlike update, read-only users pass.
     * Use update, not this, for anything that changes data.
     */
    public function viewMembersOnly(User $user, Custodian $custodian): bool
    {
        return $user->isAdmin() || $this->isMember($user, $custodian);
    }

    /**
     * Changes to this Custodian or its data (e.g. editing the Custodian, inviting,
     * approving project users, confirming access revocations): admins, or users of
     * this Custodian who are not CUSTODIAN_READ_ONLY.
     *
     * Anyone who passes update also passes viewMembersOnly, so a route never needs both.
     */
    public function update(User $user, Custodian $custodian): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->isMember($user, $custodian) && $user->custodian_user?->isReadOnly() !== true;
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
