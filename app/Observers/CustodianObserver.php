<?php

namespace App\Observers;

use App\Models\EntityModel;
use App\Models\Custodian;
use App\Models\CustodianModelConfig;

class CustodianObserver
{
    public function created(Custodian $custodian): void
    {
        // New Custodian's need all Entity models adding to their accounts
        // as a default installation
        $entityModels = EntityModel::all();
        foreach ($entityModels as $e) {
            CustodianModelConfig::create([
                'entity_model_id' => $e->id,
                'active' => 1,
                'custodian_id' => $custodian->id,
            ]);
        }
    }
}
