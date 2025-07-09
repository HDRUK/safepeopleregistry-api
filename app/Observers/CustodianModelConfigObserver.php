<?php

namespace App\Observers;

use App\Models\CustodianModelConfig;
use App\Models\Custodian;
use App\Models\ActionLog;
use Carbon\Carbon;

class CustodianModelConfigObserver
{
    /**
     * Handle the custodianModelConfig "updated" event.
     */
    public function updated(CustodianModelConfig $custodianModelConfig): void
    {
        $custodian = $custodianModelConfig->custodian;

        ActionLog::where([
            'entity_id' => $custodian->id,
            'entity_type' => Custodian::class,
            'action' => Custodian::ACTION_COMPLETE_CONFIGURATION,
        ])->whereNull('completed_at')
            ->update(['completed_at' => Carbon::now()]);
    }
}
