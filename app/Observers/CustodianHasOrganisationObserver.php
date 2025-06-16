<?php

namespace App\Observers;

use App\Models\CustodianHasOrganisation;
use App\Models\Custodian;
use App\Models\ActionLog;
use Carbon\Carbon;

class CustodianHasOrganisationObserver
{
    /**
     * Handle the CustodianHasOrganisation "created" event.
     */
    public function created(CustodianHasOrganisation $custodianHasOrganisation): void
    {
        $this->updateActionLog($custodianHasOrganisation);
    }

    /**
     * Handle the CustodianHasOrganisation "updated" event.
     */
    public function updated(CustodianHasOrganisation $custodianHasOrganisation): void
    {
        $this->updateActionLog($custodianHasOrganisation);
    }

    /**
     * Handle the CustodianHasOrganisation "deleted" event.
     */
    public function deleted(CustodianHasOrganisation $custodianHasOrganisation): void
    {
        $this->updateActionLog($custodianHasOrganisation, true);
    }

    /**
     * Handle the CustodianHasOrganisation "restored" event.
     */
    public function restored(CustodianHasOrganisation $custodianHasOrganisation): void
    {
        $this->updateActionLog($custodianHasOrganisation);
    }

    /**
     * Handle the CustodianHasOrganisation "force deleted" event.
     */
    public function forceDeleted(CustodianHasOrganisation $custodianHasOrganisation): void
    {
        $this->updateActionLog($custodianHasOrganisation, true);
    }

    /**
     * Update the action log for the custodian approval changes.
     */
    private function updateActionLog(CustodianHasOrganisation $custodianHasOrganisation, bool $isDeleting = false): void
    {
        $custodian = $custodianHasOrganisation->custodian;

        $hasApprovals = false;
        /*$custodian->approvedOrganisations()
            ->when($isDeleting, function ($query) use ($custodianHasOrganisation) {
                return $query->where(
                    'id',
                    '!=',
                    $custodianHasOrganisation->organisation_id
                );
            })
            ->exists();*/

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
