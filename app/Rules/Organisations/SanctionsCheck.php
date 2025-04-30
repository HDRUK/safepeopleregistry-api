<?php

namespace App\Rules\Organisations;

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class SanctionsCheck extends BaseRule
{
    protected $tag = SanctionsCheck::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? 'country';
        $sanctions = $conditions['sanctioned_countries'];
        $actual = Arr::get($model, $path, null);

        return in_array($actual, $sanctions);
    }
}
