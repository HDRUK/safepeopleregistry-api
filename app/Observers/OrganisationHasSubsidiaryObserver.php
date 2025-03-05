<?php

namespace App\Observers;

use App\Models\OrganisationHasSubsidiary;
use App\Models\Organisation;
use Carbon\Carbon;

class OrganisationHasSubsidiaryObserver
{
    /**
     * Handle the OrganisationHasSubsidiary "created" event.
     */
    public function created(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary->organisation);
    }

    /**
     * Handle the OrganisationHasSubsidiary "updated" event.
     */
    public function updated(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary->organisation);
    }

    /**
     * Handle the OrganisationHasSubsidiary "deleted" event.
     */
    public function deleted(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary->organisation);
    }

    /**
     * Handle the OrganisationHasSubsidiary "restored" event.
     */
    public function restored(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary->organisation);
    }

    /**
     * Handle the OrganisationHasSubsidiary "force deleted" event.
     */
    public function forceDeleted(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary->organisation);
    }

    private function updateActionLog(Organisation $organisation): void
    {
        $hasSubsidiaries = $organisation->subsidiaries()->exists();

        if ($hasSubsidiaries) {
            ActionLog::updateOrCreate(
                [
                    'entity_id' => $organisation->id,
                    'entity_type' => Organisation::class,
                    'action' => Organisation::ACTION_ADD_SUBSIDIARY_COMPLETED,
                ],
                ['completed_at' => Carbon::now()]
            );
        } else {
            ActionLog::where([
                'entity_id' => $organisation->id,
                'entity_type' => Organisation::class,
                'action' => Organisation::ACTION_ADD_SUBSIDIARY_COMPLETED,
            ])->update(['completed_at' => null]);
        }
    }
}
