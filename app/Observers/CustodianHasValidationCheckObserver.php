<?php

namespace App\Observers;

use App\Jobs\UpdateCustodianValidation;
use App\Models\CustodianHasValidationCheck;
use App\Traits\ValidationManager;

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
}
