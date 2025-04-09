<?php

namespace App\Traits;

use Exception;
use App\Models\State;
use Carbon\Carbon;

trait SearchProject
{
    public function scopeFilterByCommon($query)
    {
        $currentDate = Carbon::now()->toDateString();

        return $query->filterWhen('approved', function ($query, $value) {
            if ($value) {
                $query->whereHas('approvals');
            } else {
                $query->whereDoesntHave('approvals');
            }
        })
        ->filterWhen('pending', function ($query, $pending) {
            if ($pending) {
                $query->whereDoesntHave('approvals');
            } else {
                $query->whereHas('approvals');
            }
        })
        ->filterWhen('active', function ($query, $active) use ($currentDate) {
            if ($active) {
                $query->where('start_date', '>=', $currentDate)->where('end_date', '>=', $currentDate);
            } else {
                $query->where('start_date', '<', $currentDate)->where('end_date', '>', $currentDate);
            }
        })
        ->filterWhen('completed', function ($query, $completed) use ($currentDate) {
            if ($completed) {
                $query->where('end_date', '>=', $currentDate);
            } else {
                $query->where('end_date', '<', $currentDate);
            }
        });
    }
}
