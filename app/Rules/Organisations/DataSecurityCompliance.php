<?php

namespace App\Rules\Organisations;

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class DataSecurityCompliance extends BaseRule
{
    protected $tag = DataSecurityCompliance::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? [];
        $expects = $conditions['expects'] ?? null;
        $actual = Arr::get($model, $path, null);

        return $actual == $expects;
    }
}
