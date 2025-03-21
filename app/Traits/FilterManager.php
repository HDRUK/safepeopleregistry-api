<?php

namespace App\Traits;

use Exception;
use App\Models\State;

trait FilterManager
{
    public function scopeFilterByState($query)
    {
        $stateSlug = \request()->only(['filter']);
        if (!$stateSlug) {
            return;
        }

        if (!in_array($stateSlug['filter'], State::STATES)) {
            throw new Exception('filter state \"' . $stateSlug['filter'] . '\" is unknown and must be one of: '
                . implode(', ', State::STATES));
        }

        $state = State::where('slug', $stateSlug['filter'])->first();
        if (!$state) {
            return;
        }

        return $query->whereHas('modelState', function ($query) use ($state) {
            $query->where('state_id', $state->id);
        });
    }
}
