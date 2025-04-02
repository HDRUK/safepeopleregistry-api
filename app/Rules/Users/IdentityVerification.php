<?php

namespace App\Rules\Users;

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class IdentityVerification extends BaseRule
{
    protected $tag = IdentityVerification::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? 'registry.identity.idvt_result';
        $expected = $conditions['expected'] ?? 1;

        $actual = Arr::get($model, $path, null);

        return ($actual === $expected);
    }
}
