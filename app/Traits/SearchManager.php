<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;

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

    public function scopeSortViaRequest($query): mixed
    {
        if ($sort = \request()->get('sort')) {
            [$field, $direction] = explode(':', $sort) + [null, 'asc'];

            if (!in_array(strtolower($field), static::$sortableColumns, true)) {
                throw ValidationException::withMessages([
                    'sort' => "Invalid sort field: $field."
                ]);
            }

            if (!in_array(strtolower($direction), ['asc', 'desc'], true)) {
                throw ValidationException::withMessages([
                    'sort' => "Invalid sort direction: $direction. Must be 'asc' or 'desc'."
                ]);
            }

            $query->orderBy(strtolower($field), strtolower($direction));

        }

        return $query;
    }
}
