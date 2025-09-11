<?php

namespace App\Policies;

use App\Models\Organisation;
use App\Models\User;

class OrganisationPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Organisation $organisation): bool
    {
        return $user->isAdmin() || ($user->inGroup([User::GROUP_ORGANISATIONS]) &&
            !$user->is_delegate && $user->organisation_id === $organisation->id);
    }

    public function delete(User $user, Organisation $organisation): bool
    {
        return $this->update($user, $organisation);
    }

    public function viewDetailed(User $user, Organisation $organisation): bool
    {
        return $user->isAdmin() || $user->inGroup([User::GROUP_CUSTODIANS]) ||
            ($user->inGroup([User::GROUP_ORGANISATIONS]) && $user->organisation_id === $organisation->id);
    }

    public function updateIsOrganisation(User $user): bool
    {
        return $user->isOrganisation();
    }
}
