<?php

namespace App\Observers;

use App\Models\CustodianHasValidationCheck;
use App\Models\Organisation;
use App\Traits\ValidationManager;

class CustodianHasValidationCheckObserver
{
    use ValidationManager;

    public function saved(CustodianHasValidationCheck $model): void
    {
        $custodianId = $model->custodian_id;
        $this->updateAllCustodianOrganisationValidation(
            $custodianId,
        );
    }
}
