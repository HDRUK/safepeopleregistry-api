<?php

namespace App\Observers;

use App\Models\OrganisationHasCustodianApproval;
use App\Models\Custodian;
use App\Models\ActionLog;
use Carbon\Carbon;

class OrganisationHasCustodianApprovalObserver
{
    /**
     * Handle the OrganisationHasCustodianApproval "created" event.
     */
    public function created(OrganisationHasCustodianApproval $organisationHasCustodianApproval): void
    {
        $this->updateActionLog($organisationHasCustodianApproval);
    }

    /**
     * Handle the OrganisationHasCustodianApproval "updated" event.
     */
    public function updated(OrganisationHasCustodianApproval $organisationHasCustodianApproval): void
    {
        $this->updateActionLog($organisationHasCustodianApproval);
    }

    /**
     * Handle the OrganisationHasCustodianApproval "deleted" event.
     */
    public function deleted(OrganisationHasCustodianApproval $organisationHasCustodianApproval): void
    {
        $this->updateActionLog($organisationHasCustodianApproval, true);
    }

    /**
     * Handle the OrganisationHasCustodianApproval "restored" event.
     */
    public function restored(OrganisationHasCustodianApproval $organisationHasCustodianApproval): void
    {
        $this->updateActionLog($organisationHasCustodianApproval);
    }

    /**
     * Handle the OrganisationHasCustodianApproval "force deleted" event.
     */
    public function forceDeleted(OrganisationHasCustodianApproval $organisationHasCustodianApproval): void
    {
        $this->updateActionLog($organisationHasCustodianApproval, true);
    }

    /**
     * Update the action log for the custodian approval changes.
     */
    private function updateActionLog(OrganisationHasCustodianApproval $organisationHasCustodianApproval, bool $isDeleting = false): void
    {
        $custodian = $organisationHasCustodianApproval->custodian;

        $hasApprovals = $custodian->approvedOrganisations()
            ->when($isDeleting, function ($query) use ($organisationHasCustodianApproval) {
                return $query->where(
                    'id',
                    '!=',
                    $organisationHasCustodianApproval->id
                );
            })
            ->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $custodian->id,
                'entity_type' => Custodian::class,
                'action' => Custodian::ACTION_ADD_ORGANISATIONS,
            ],
            ['completed_at' => $hasApprovals ? Carbon::now() : null]
        );
    }
}
