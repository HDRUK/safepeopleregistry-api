<?php

namespace App\Rules\Users;

use Carbon\Carbon;
use App\Rules\BaseRule;
use Illuminate\Support\Arr;

class TrainingRule extends BaseRule
{
    protected $tag = TrainingRule::class;

    public function evaluate($model, array $conditions): bool
    {
        $path = $conditions['path'] ?? 'registry.trainings';
        $trainingArray = Arr::get($model, $path, null);

        foreach ($trainingArray as $t) {
            // We OR this, in case other providers offer similar named training
            // that we don't know about, rather than fail immediately
            if (in_array($t['provider'], $conditions['expects']['provider'])
                || in_array($t['training_name'], $conditions['expects']['provider'])) {
                    // We only need 1 non-expired training to pass ruling
                    $then = $t['expires_at'];
                    $now = Carbon::now();

                    return $now->lessThan($then);
            }
        }

        return false;
    }
}