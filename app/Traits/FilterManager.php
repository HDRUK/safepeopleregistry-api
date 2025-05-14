<?php

namespace App\Traits;

use Exception;
use App\Models\State;

trait FilterManager
{
    public function scopeFilterByState($query)
    {
        $stateSlugs = \request()->input('filter');

        if (empty($stateSlugs)) {
            return $query;
        }

        $stateSlugs = is_array($stateSlugs) ? $stateSlugs : [$stateSlugs];

        $invalidSlugs = array_diff($stateSlugs, State::STATES);
        if (!empty($invalidSlugs)) {
            throw new \Exception('Unknown state filters: ' . implode(', ', $invalidSlugs) .
                '. Valid states are: ' . implode(', ', State::STATES));
        }

        $states = State::whereIn('slug', $stateSlugs)->pluck('id');

        if ($states->isEmpty()) {
            return $query;
        }

        return $query->whereHas('modelState', function ($query) use ($states) {
            $query->whereIn('state_id', $states);
        });
    }
}
