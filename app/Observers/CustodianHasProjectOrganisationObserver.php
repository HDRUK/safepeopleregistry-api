<?php

namespace App\Observers;

use App\Models\CustodianHasProjectOrganisation;
use App\Models\ActionLog;
use App\Models\Custodian;
use App\Models\State;
use Carbon\Carbon;

class CustodianHasProjectOrganisationObserver
{
    public function saved(CustodianHasProjectOrganisation $model): void
    {
        $this->checkCustodianValidationStatus($model);
    }

    public function deleted(CustodianHasProjectOrganisation $model): void
    {
        $this->checkCustodianValidationStatus($model);
    }

    protected function checkCustodianValidationStatus(CustodianHasProjectOrganisation $model): void
    {
        $custodian = $model->custodian;

        if (!$custodian) {
            return;
        }

        $hasApprovals = $custodian
            ->projectOrganisations()
            ->whereHas('modelState.state', function ($query) {
                $query->where('slug', State::STATE_VALIDATED);
            })
            ->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $custodian->id,
                'entity_type' => Custodian::class,
                'action' => Custodian::ACTION_APPROVE_AN_ORGANISATION,
            ],
            [
                'completed_at' => $hasApprovals ? Carbon::now() : null
            ]
        );
    }
}
