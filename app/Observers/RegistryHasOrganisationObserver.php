<?php

namespace App\Observers;

use App\Models\RegistryHasOrganisation;
use App\Models\ActionLog;
use App\Models\Organisation;
use Carbon\Carbon;

class RegistryHasOrganisationObserver
{
    /**
     * Handle the RegistryHasOrganisation "created" event.
     */
    public function created(RegistryHasOrganisation $registryHasOrganisation): void
    {
        $this->updateActionLog($registryHasOrganisation);
    }

    /**
     * Handle the RegistryHasOrganisation "updated" event.
     */
    public function updated(RegistryHasOrganisation $registryHasOrganisation): void
    {
        $this->updateActionLog($registryHasOrganisation);
    }

    /**
     * Handle the RegistryHasOrganisation "deleted" event.
     */
    public function deleted(RegistryHasOrganisation $registryHasOrganisation): void
    {
        $this->updateActionLog($registryHasOrganisation);
    }

    /**
     * Handle the RegistryHasOrganisation "restored" event.
     */
    public function restored(RegistryHasOrganisation $registryHasOrganisation): void
    {
        $this->updateActionLog($registryHasOrganisation);
    }

    /**
     * Handle the RegistryHasOrganisation "force deleted" event.
     */
    public function forceDeleted(RegistryHasOrganisation $registryHasOrganisation): void
    {
        $this->updateActionLog($registryHasOrganisation);
    }

    /**
     * Updates the action log based on the organisation's registry associations.
     */
    private function updateActionLog(RegistryHasOrganisation $registryHasOrganisation): void
    {
        $organisation = $registryHasOrganisation->organisation;
        if (!$organisation) {
            return;
        }

        $hasAssociations = RegistryHasOrganisation::where('organisation_id', $organisation->id)->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $organisation->id,
                'entity_type' => Organisation::class,
                'action' => Organisation::ACTION_AFFILIATE_EMPLOYEES_COMPLETED,
            ],
            ['completed_at' => $hasAssociations ? Carbon::now() : null]
        );
    }
}
