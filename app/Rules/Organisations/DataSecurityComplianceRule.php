<?php

use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class DataSecurityComplianceRule extends BaseRule
{
    protected $tag = DataSecurityComplianceRule::class;

    public function evaluate($model, array $conditions): bool
    {
        // $path = $conditions['path'] ?? [];
        // dd('here');
        
        // foreach ($path as $p) {
        //     $actual = Arr::get($model, $p, null);
        //     dd($actual);
        // }
    }
}