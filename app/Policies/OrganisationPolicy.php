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

    /**
     * Visibility lives in Organisation::scopeVisibleTo so lists and single records agree.
     */
    public function view(User $user, Organisation $organisation): bool
    {
        return $organisation->isVisibleTo($user);
    }

    /**
     * Anyone who can see the Organisation, plus the User who created a restricted one until it
     * has been invited: with SroRequirementEnabled on, a researcher invites the Organisation
     * they've just added before their affiliation to it exists.
     */
    public function invite(User $user, Organisation $organisation): bool
    {
        return $this->view($user, $organisation)
            || ($organisation->created_by_user_id === $user->id && !$organisation->hasBeenInvited());
    }

    public function viewDetailed(User $user, Organisation $organisation): bool
    {
        if (!$this->view($user, $organisation)) {
            return false;
        }

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
            OrganisationRole::Delegate => !$organisation->hasClaimedSro(),
        };
    }

    /**
     * Admins and any member of the organisation (SRO or delegate) can remove a delegate,
     * except that nobody can remove themselves. Which users are removable is enforced by
     * OrganisationMembershipService::removeDelegate (delegates only, never the SRO).
     */
    public function removeDelegate(User $user, Organisation $organisation, int $delegateId): bool
    {
        if ($user->id === $delegateId) {
            return false;
        }

        return $user->isAdmin()
            || ($user->organisationRole() !== null && $user->organisation_id === $organisation->id);
    }

    /**
     * Admins and members of the organisation (SRO or delegate) can invite users into it.
     */
    public function inviteUser(User $user, Organisation $organisation): bool
    {
        return $user->isAdmin()
            || ($user->organisationRole() !== null && $user->organisation_id === $organisation->id);
    }

    public function updateIsOrganisation(User $user): bool
    {
        return $user->isOrganisation();
    }
}
