<?php

namespace App\Observers;

use App\Jobs\UpdateCustodianValidation;
use App\Models\ActionLog;
use App\Models\Custodian;
use App\Models\ValidationCheck;
use App\Traits\ValidationManager;
use Carbon\Carbon;

class ValidationCheckObserver
{
    use ValidationManager;

    public function saved(ValidationCheck $model): void
    {
        $custodianId = $model->custodian_id;
        UpdateCustodianValidation::dispatch(
            $custodianId,
            $model->applies_to
        );
    }

    public function updated(ValidationCheck $model): void
    {
        $custodianId = $model->custodian_id;

        ActionLog::where([
            'entity_id' => $custodianId,
            'entity_type' => Custodian::class,
            'action' => Custodian::ACTION_COMPLETE_CONFIGURATION,
        ])->whereNull('completed_at')
            ->update(['completed_at' => Carbon::now()]);
    }
}
