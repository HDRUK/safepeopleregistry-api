<?php

namespace App\Traits;

use RulesEngineManagementController as REMC;

/**
 * RulesEngineManager
 *
 * Allows implementing classes to call the RulesEngineManager to
 * automatically apply decision models on calling controllers
 */
trait RulesEngineManager
{
    public function applyRulesEngine(array $input): array
    {
        $decisionTree = REMC::callRulesEngine($input);

        dd($decisionTree);
    }
}
