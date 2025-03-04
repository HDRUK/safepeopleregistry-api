<?php

namespace App\Observers;

use App\Models\RegistryHasAffiliation;
use App\Models\ActionLog;
use App\Models\User;
use Carbon\Carbon;

class RegistryHasAffiliationObserver
{
    /**
     * Handle the RegistryHasAffiliation "created" event.
     */
    public function created(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation);
    }

    /**
     * Handle the RegistryHasAffiliation "updated" event.
     */
    public function updated(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation);
    }

    /**
     * Handle the RegistryHasAffiliation "deleted" event.
     */
    public function deleted(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation);
    }

    /**
     * Handle the RegistryHasAffiliation "restored" event.
     */
    public function restored(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation);
    }

    /**
     * Handle the RegistryHasAffiliation "force deleted" event.
     */
    public function forceDeleted(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation);
    }

    /**
     * Updates the action log based on the user's affiliations.
     */
    private function updateActionLog(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $registryId = $registryHasAffiliation->registry_id;
        $user = User::where('registry_id', $registryId)->first();
        if (!$user) {
            return;
        }
        $hasAffiliations = RegistryHasAffiliation::where('registry_id', $registryId)->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $user->id,
                'entity_type' => User::class,
                'action' => User::ACTION_AFFILIATIONS_COMPLETE,
            ],
            ['completed_at' => $hasAffiliations ? Carbon::now() : null]
        );
    }

}
