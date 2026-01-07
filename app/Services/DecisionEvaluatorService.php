<?php

namespace App\Services;

use RulesEngineManagementController as REMC;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;

class DecisionEvaluatorService
{
    private $custodianRules = [];

    public function __construct(array $validationType, ?int $custodianId = null)
    {
        $this->custodianRules = REMC::loadCustodianRules($validationType, $custodianId);
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
        $types = [];

        $modelClass = get_class($model);
        $results = [];

        foreach ($this->custodianRules as $rule) {
            // We want to ensure that we're only running rules against their
            // intended class types, such as App\Model\User against
            $normModelType = $this->normaliseRuleClass($rule->model_type);
            if ($normModelType === User::GROUP_USERS && $normModelType !== $model->user_group) {
                continue;
            }

            $results[] = $this->assessRule($rule, $model);
        }

        return $results;
    }

    private function normaliseRuleClass(string $className): string
    {
        $baseName = class_basename($className);
        return Str::upper(Str::plural(Str::snake($baseName)));
    }

    private function assessRule($rule, $model): array
    {
        $retVal = [];
        $actual = null;
        $ruleClass = app($rule->rule_class);
        $conditions = json_decode($rule->conditions, true);

        if (isset($conditions['path']) && is_array($conditions['path'])) {
            $actual = array_map(fn ($key) => Arr::get($model, $key, null), $conditions['path']);
        } else {
            $actual = Arr::get($model, $conditions['path'], null);
        }

        if (!$ruleClass->evaluate($model, $conditions)) {
            $retVal['passed'] = false;
            $retVal['ruleId'] = $rule->id;
            $retVal['failed_rules'] = [
                'rule' => class_basename($ruleClass),
                'status' => 'failed',
                'conditions' => $conditions,
                'actual' => is_array($actual) ? json_encode($actual) : $actual,
            ];
        } else {
            $retVal = [
                'ruleId' => $rule->id,
                'rule' => class_basename($ruleClass),
                'conditions' => $conditions,
                'actual' => is_array($actual) ? json_encode($actual) : $actual,
                'status' => true,
            ];
        }

        return $retVal;
    }
}
