<?php

namespace App\Traits;

use Exception;
use App\Models\State;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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

    // public function applyFilters(Builder $query, Request $request): Builder
    // {
    //     $stateSlug = \request()->only(['filter']);
    //     if (!$stateSlug) {
    //         return;
    //     }

    //     if (!in_array($stateSlug['filter'], State::STATES)) {
    //         throw new Exception('filter state \"' . $stateSlug['filter'] .'\" is unknown and must be one of: '
    //             . implode(', ', State::STATES));
    //     }

    //     $state = State::where('slug', $stateSlug['filter'])->first();
    //     if (!$state) {
    //         return;
    //     }

    //     return $query->whereHas('modelState', function ($query) use ($state) {
    //         $qu
    //     });

    //     if ($request->has('filter')) {
    //         $filters = $request->input('filter');

    //         if (isset($filters['state'])) {
    //             $query->whereHas('modelState', function (Builder $q) use ($filters) {
    //                 $q->where('state_id', State::where('slug', $filters['state'])->first());
    //             });
    //         }

    //         // ...to do, add other filters that aren't related to a models' 'state'
    //     }

    //     return $query;
    // }
}
