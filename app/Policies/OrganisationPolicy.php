<?php

namespace App\Policies;

use App\Enums\OrganisationRole;
use App\Models\Organisation;
use App\Models\User;

class OrganisationPolicy
{
    public function update(User $user, Organisation $organisation): bool
    {
        return $user->isAdmin() ||
            (
                $user->inGroup([User::GROUP_ORGANISATIONS]) &&
                ($user->is_delegate || $user->is_sro) &&
                $user->organisation_id === $organisation->id
            );
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

    /**
     * Actions delegates can take until the organisation has a claimed SRO, after which only the SRO can.
     */
    public function performSroRestrictedAction(User $user, Organisation $organisation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $role = $user->organisationRole();

        if ($role === null || $user->organisation_id !== $organisation->id) {
            return false;
        }

        return match ($role) {
            OrganisationRole::Sro => true,
            OrganisationRole::Delegate => !$organisation->hasSro(),
        };
    }

    public function updateIsOrganisation(User $user): bool
    {
        return $user->isOrganisation();
    }
}
