<?php

namespace App\Observers;

use App\Models\UserHasDepartments;
use App\Models\User;
use Carbon\Carbon;

class UserHasDepartmentsObserver
{
    /**
     * Handle the UserHasDepartments "created" event.
     */
    public function created(UserHasDepartments $userHasDepartments): void
    {
        $this->checkAndUpdateOrganisationUser($userHasDepartments);
    }

    /**
     * Handle the UserHasDepartments "updated" event.
     */
    public function updated(UserHasDepartments $userHasDepartments): void
    {
        $this->checkAndUpdateOrganisationUser($userHasDepartments);
    }

    /**
     * Handle the UserHasDepartments "deleted" event.
     */
    public function deleted(UserHasDepartments $userHasDepartments): void
    {
        $this->checkAndUpdateOrganisationUser($userHasDepartments);
    }

    /**
     * Handle the UserHasDepartments "restored" event.
     */
    public function restored(UserHasDepartments $userHasDepartments): void
    {
        $this->checkAndUpdateOrganisationUser($userHasDepartments);
    }

    /**
     * Handle the UserHasDepartments "force deleted" event.
     */
    public function forceDeleted(UserHasDepartments $userHasDepartments): void
    {
        $this->checkAndUpdateOrganisationUser($userHasDepartments);
    }

    /**
     * Check if the user is an organisation user and if they have at least one department.
     */
    private function checkAndUpdateOrganisationUser(UserHasDepartments $userHasDepartments): void
    {
        $user = $userHasDepartments->user;

        if ($this->isOrganisationUser($user)) {
            $organisation = $user->organisation;
            $hasDepartments = $user->departments()->exists();

            if ($hasDepartments) {
                ActionLog::updateOrCreate(
                    [
                        'entity_id' => $organisation->id,
                        'entity_type' => Organisation::class,
                        'action' => Organisation::ACTION_ADD_SRO_COMPLETED,
                    ],
                    ['completed_at' => Carbon::now()]
                );
            } else {
                ActionLog::updateOrCreate(
                    [
                        'entity_id' => $organisation->id,
                        'entity_type' => Organisation::class,
                        'action' => Organisation::ACTION_ADD_SRO_COMPLETED,
                    ],
                    ['completed_at' => null]
                );
            }
        }
    }

    /**
     * Determine if a user is an organisation user.
     */
    private function isOrganisationUser(?User $user): bool
    {
        return $user &&
               $user->organisation_id !== null &&
               $user->user_group === 'ORGANISATIONS' &&
               $user->is_org_admin === 1;
    }
}
