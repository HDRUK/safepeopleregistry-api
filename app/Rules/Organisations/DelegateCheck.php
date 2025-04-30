<?php

namespace App\Rules\Organisations;

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class DelegateCheck extends BaseRule
{
    protected $tag = DelegateCheck::class;

    public function evaluate($model, array $conditions): bool
    {
        return true;
    }
}
