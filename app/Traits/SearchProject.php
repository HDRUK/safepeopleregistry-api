<?php

namespace App\Traits;

use Carbon\Carbon;

trait SearchProject
{
    public function scopeFilterByCommon($query)
    {
        $currentDate = Carbon::now()->toDateString();

        return $query->filterWhen('active', function ($query, $active) use ($currentDate) {
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
