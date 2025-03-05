<?php

namespace App\Observers;

use App\Models\OrganisationHasSubsidiary;
use App\Models\Organisation;
use App\Models\ActionLog;
use Carbon\Carbon;

class OrganisationHasSubsidiaryObserver
{
    /**
     * Handle the OrganisationHasSubsidiary "created" event.
     */
    public function created(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary);
    }

    /**
     * Handle the OrganisationHasSubsidiary "updated" event.
     */
    public function updated(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary);
    }

    /**
     * Handle the OrganisationHasSubsidiary "deleted" event.
     */
    public function deleted(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary, true);
    }

    /**
     * Handle the OrganisationHasSubsidiary "restored" event.
     */
    public function restored(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary);
    }

    /**
     * Handle the OrganisationHasSubsidiary "force deleted" event.
     */
    public function forceDeleted(OrganisationHasSubsidiary $organisationHasSubsidiary): void
    {
        $this->updateActionLog($organisationHasSubsidiary, true);
    }

    private function updateActionLog(OrganisationHasSubsidiary $organisationHasSubsidiary, bool $isDeleting = false): void
    {
        $organisation = $organisationHasSubsidiary->organisation;
        $hasSubsidiaries = $organisation->subsidiaries()
            ->when($isDeleting, function ($query) use ($organisationHasSubsidiary) {
                return $query->where(
                    'subsidiary_id',
                    '!=',
                    $organisationHasSubsidiary->subsidiary_id
                );
            })
            ->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $organisation->id,
                'entity_type' => Organisation::class,
                'action' => Organisation::ACTION_ADD_SUBSIDIARY_COMPLETED,
            ],
            ['completed_at' => $hasSubsidiaries ? Carbon::now() : null]
        );

    }
}
