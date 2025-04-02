<?php

namespace App\Services;

use RulesEngineManagementController as REMC;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class DecisionEvaluatorService
{
    private $custodianRules = [];

    public function __construct(Request $request)
    {
        $this->custodianRules = REMC::loadCustodianRules($request);
    }

    public function evaluate($models, $batch = false)
    {
        if (!$this->custodianRules) {
            return;
        }

        if (!$batch) {
            return $this->evaluateSingle($models);
        }

        $results = [];
        foreach ($models as $model) {
            $results[$model->id] = $this->evaluateSingle($model);
        }

        return $results;
    }

    public function evaluateSingle($model)
    {
        $modelClass = get_class($model);
        $userResults = [
            'passed' => true,
            'failed_rules' => []
        ];

        foreach ($this->custodianRules as $rule) {
            // We want to ensure that we're only running rules against their
            // intended class types, such as App\Model\User against
            if ($this->normaliseRuleClass($rule->model_type) !== $model->user_group) {
                continue;
            }

            $ruleClass = app($rule->rule_class);
            $conditions = json_decode($rule->conditions, true);

            if (!$ruleClass->evaluate($model, $conditions)) {
                $userResults['passed'] = false;
                $userResults['failed_rules'][] = [
                    'rule' => class_basename($ruleClass), // Short class name
                    'status' => "failed",
                    'conditions' => $conditions,
                    'actual' => Arr::get($model, json_decode($rule->conditions, true)['path'], null),
                ];
            }
        }

        return $userResults;
    }

    private function normaliseRuleClass(string $className): string
    {
        $baseName = class_basename($className);
        return Str::upper(Str::plural(Str::snake($baseName)));
    }
}
