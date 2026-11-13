<?php

namespace App\Policies;

use App\Models\ActionLog;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Custodian;

class ActionLogPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActionLog $actionLog): bool
    {
        $entityType = $actionLog->entity_type->value;
        $entityId = $actionLog->entity_id;

        if ($user->isAdmin()) {
            return true;
        }

        switch ($entityType) {
            case User::class:
                return $user->id === $entityId;

            case Organisation::class:
                return  $user->organisation_id === $entityId;

            case Custodian::class:
                return optional($user->custodian_user)->custodian_id === $entityId;

            default:
                return false;
        }
    }

    public function update(User $user, ActionLog $actionLog): bool
    {
        // Read-only custodian users can't tick their Custodian's checklist; their own
        // user checklist is like their own profile, which they can still update
        if (
            $actionLog->entity_type->value === Custodian::class &&
            $user->custodian_user?->isReadOnly() === true
        ) {
            return false;
        }

        return $this->view($user, $actionLog);
    }
}
