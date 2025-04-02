<?php

namespace App\Rules\Users;

use Carbon\Carbon;
use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class Training extends BaseRule
{
    protected $tag = Training::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? 'registry.trainings';
        $trainingArray = Arr::get($model, $path, null);

        foreach ($trainingArray as $t) {
            if ($t['training_name'] === $conditions['expects']) {
                $then = $t['expires_at'];
                $now = Carbon::now();

                return $now->lessThan($then);
            }
        }

        return false;
    }
}
