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

    public function scopeApplySorting($query): mixed
    {
        $input = \request()->all();
        // If no sort option passed, then always default to the first element
        // of our sortableColumns array on the model
        $sort = isset($input['sort']) ? $input['sort'] : static::$sortableColumns[0] . ':asc';

        $tmp = explode(':', $sort);
        $field = strtolower($tmp[0]);

        if (isset(static::$sortableColumns) && !in_array(strtolower($field), static::$sortableColumns)) {
            throw new \InvalidArgumentException('field ' . $field . ' is not sortable.');
        }

        $direction = strtolower($tmp[1]);
        if (!in_array($direction, ['asc', 'desc'])) {
            throw new \InvalidArgumentException('invalid sort direction ' . $direction);
        }

        return $query->orderBy($field, $direction);
    }
}
