<?php

namespace App\Rules\Users;

use App\Rules\BaseRule;

class NHSSDETerms extends BaseRule
{
    protected $tag = NHSSDETerms::class;

    public function evaluate($model, array $conditions): bool
    {
        // Stub for later SDE work
        return true;
    }
}
