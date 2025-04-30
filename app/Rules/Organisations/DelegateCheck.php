<?php

namespace App\Rules\Organisations;

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class DelegateCheck extends BaseRule
{
    protected $tag = DelegateCheck::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? 'is_delegate';
        $expected = $conditions['expects'] ??  ['minimum' => 1];
        $actual = Arr::get($model, $path, null);

        $actualCount = is_array($actual) ? count($actual) : 0;
        return $actualCount >= ($expected['minimum'] ?? 1);
    }
}
