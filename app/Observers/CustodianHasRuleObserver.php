<?php

namespace App\Observers;

use App\Models\CustodianHasRule;
use App\Models\Custodian;
use App\Models\ActionLog;
use Carbon\Carbon;

class CustodianHasRuleObserver
{
    /**
     * Handle the CustodianHasRule "created" event.
     */
    public function created(CustodianHasRule $custodianHasRule): void
    {
        $this->updateActionLog($custodianHasRule);
    }

    /**
     * Handle the CustodianHasRule "updated" event.
     */
    public function updated(CustodianHasRule $custodianHasRule): void
    {
        $this->updateActionLog($custodianHasRule);
    }

    /**
     * Handle the CustodianHasRule "deleted" event.
     */
    public function deleted(CustodianHasRule $custodianHasRule): void
    {
        $this->updateActionLog($custodianHasRule);
    }

    /**
     * Handle the CustodianHasRule "restored" event.
     */
    public function restored(CustodianHasRule $custodianHasRule): void
    {
        $this->updateActionLog($custodianHasRule);
    }

    /**
     * Handle the CustodianHasRule "force deleted" event.
     */
    public function forceDeleted(CustodianHasRule $custodianHasRule): void
    {
        $this->updateActionLog($custodianHasRule);
    }

    private function updateActionLog(CustodianHasRule $custodianHasRule): void
    {
        // they've done something, so log it as complete
        // - this will do for now;
        $custodian = $custodianHasRule->custodian;
        ActionLog::updateOrCreate(
            [
                'entity_id' => $custodian->id,
                'entity_type' => Custodian::class,
                'action' => Custodian::ACTION_COMPLETE_CONFIGURATION,
            ],
            ['completed_at' => Carbon::now() ]
        );
    }
}
