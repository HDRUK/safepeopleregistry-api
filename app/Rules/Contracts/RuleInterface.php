<?php

namespace App\Rules\Contracts;

interface RuleInterface
{
    public function evaluate($model, array $conditions): bool|array;
}
