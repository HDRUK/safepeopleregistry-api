<?php

namespace App\Observers;

use App\Models\DecisionModel;
use App\Models\Custodian;
use App\Models\CustodianModelConfig;
use App\Models\ActionLog;

class CustodianObserver
{
    public function created(Custodian $custodian): void
    {
        // New Custodian's need all Entity models adding to their accounts
        // as a default installation
        $decisionModels = DecisionModel::all();
        foreach ($decisionModels as $d) {
            CustodianModelConfig::updateOrCreate([
                'entity_model_id' => $d->id,
                'active' => 1,
                'custodian_id' => $custodian->id,
            ]);
        }

        foreach (Custodian::getDefaultActions() as $action) {
            ActionLog::firstOrCreate([
                 'entity_id' => $custodian->id,
                 'entity_type' => Custodian::class,
                 'action' => $action,
            ], [
                 'completed_at' => null,
             ]);
        }
    }
}
