<?php

namespace App\Rules\Users;

use Carbon\Carbon;
use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class NHSSDETerms extends BaseRule
{
    protected $tag = NHSSDETerms::class;

    public function evaluate($model, array $conditions): bool
    {
        // Stub for later SDE work
        return true;
    }
}