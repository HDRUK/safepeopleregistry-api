<?php

namespace App\Rules\Users;

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class UKDataProtection extends BaseRule
{
    protected $tag = UKDataProtection::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? 'location';
        $sanctions = $conditions['sanctioned_countries'];

        $actual = Arr::get($model, $path, null);

        $verdict = $actual ? !in_array($actual, $sanctions) : false;

        return $verdict;
    }
}
