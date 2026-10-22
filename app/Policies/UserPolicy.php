<?php

namespace App\Policies;

use App\Enums\OrganisationRole;
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

        $userRole = $user->organisationRole();
        $modelRole = $model->organisationRole();

        if (
            $userRole !== null &&
            $modelRole !== null &&
            $user->organisation_id === $model->organisation_id
        ) {
            return match ($userRole) {
                // SROs can update anyone in the same org
                OrganisationRole::Sro => true,
                // Delegates can only update other delegates, including themselves
                OrganisationRole::Delegate => $modelRole === OrganisationRole::Delegate,
            };
        }

        // others they can self-update
        return $user->id === $model->id;
    }

    public function invite(User $user): bool
    {
        return $this->viewAny($user); // same access logic as viewAny for now
    }

    public function updateEmailFromInvite(User $user, User $model)
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->user_group === User::GROUP_ORGANISATIONS && !isset($user->keycloak_id);
    }
}
