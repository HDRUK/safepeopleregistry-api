<?php

namespace App\Rules\Users;

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class IdentityVerificationRule extends BaseRule
{
    protected $tag = IdentityVerificationRule::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? 'registry.identity.idvt_result';
        $expected = $conditions['expected'] ?? 1;

        $actual = Arr::get($model, $path, null);

        return ($actual === $expected);
    }
}
