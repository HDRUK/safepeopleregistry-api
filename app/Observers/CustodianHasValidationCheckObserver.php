<?php

namespace App\Observers;

use App\Jobs\UpdateCustodianValidation;
use App\Models\ActionLog;
use App\Models\Custodian;
use App\Models\CustodianHasValidationCheck;
use App\Traits\ValidationManager;
use Carbon\Carbon;

class CustodianHasValidationCheckObserver
{
    use ValidationManager;

    public function saved(CustodianHasValidationCheck $model): void
    {
        $custodianId = $model->custodian_id;
        UpdateCustodianValidation::dispatch(
            $custodianId,
            $model->validationCheck->applies_to
        );
    }

    public function updated(CustodianHasValidationCheck $model): void
    {
        $custodian = $model->custodian;
        dump('seedng...');

        ActionLog::where([
            'entity_id' => $custodian->id,
            'entity_type' => Custodian::class,
            'action' => Custodian::ACTION_COMPLETE_CONFIGURATION,
        ])->whereNull('completed_at')
            ->update(['completed_at' => Carbon::now()]);
    }
}
