<?php

namespace App\Rules\Users;

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class UKDataProtectionRule extends BaseRule
{
    protected $tag = UKDataProtectionRule::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? 'location';
        $sanctions = $condition['sanctioned_countries'] ?? [];

        $actual = Arr::get($model, $path, null);

        return in_array($actual, $sanctions);
    }
}
