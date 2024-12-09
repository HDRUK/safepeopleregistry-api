<?php

namespace App\Traits;

/**
 * SearchManager
 *
 * Add this to an App\Model to use the magic scoped
 * searchViaRequest call, which will automatically take
 * the incoming request (from query string) parameters
 * and search for them against the db record
 */
trait SearchManager
{
    public function scopeSearchViaRequest($query): mixed
    {
        $input = \request()->all();

        return $query->where(function ($query) use ($input) {
            foreach ($input as $field => $searchValue) {
                if (!in_array(strtolower($field), static::$searchableColumns)) {
                    continue;
                }

                foreach ($searchValue as $term) {
                    $query->orWhere(strtolower($field), 'LIKE', '%' . $term . '%');
                }
            }
        });
    }
}
