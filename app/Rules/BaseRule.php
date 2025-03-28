<?php

namespace App\Rules;

use App\Rules\Contracts\RuleInterface;

abstract class BaseRule implements RuleInterface
{
    public function meetsCondition($model, $conditions): bool
    {
        foreach ($conditions as $field => $expectedValue) {
            if ($model->{$field} != $expectedValue) {
                return false;
            }
        }

        return true;
    }
}
