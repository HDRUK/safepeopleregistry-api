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
/**
 * Trait SearchManager
 *
 * @method static Builder searchViaRequest(array|null $input = null)
 */
trait SearchManager
{
    public function scopeSearchViaRequest($query, ?array $input = null): mixed
    {
        $input = $input ?? request()->all();

        $orGroups = [];
        $andGroups = [];

        foreach ($input as $fieldWithOperator => $searchValues) {
            if (str_ends_with($fieldWithOperator, '__or')) {
                $field = str_replace('__or', '', $fieldWithOperator);
                $logic = 'or';
            } elseif (str_ends_with($fieldWithOperator, '__and')) {
                $field = str_replace('__and', '', $fieldWithOperator);
                $logic = 'and';
            } else {
                $field = $fieldWithOperator;
                $logic = 'or';
            }

            if (!in_array(strtolower($field), static::$searchableColumns)) {
                continue;
            }

            if ($logic === 'or') {
                $orGroups[$field] = $searchValues;
            } else {
                $andGroups[$field] = $searchValues;
            }
        }

        return $query->where(function ($outerQuery) use ($orGroups, $andGroups) {

            foreach ($andGroups as $field => $terms) {
                $outerQuery->where(function ($q) use ($field, $terms) {
                    foreach ($terms as $term) {
                        $q->where($field, 'LIKE', '%' . $term . '%');
                    }
                });
            }

            if (!empty($orGroups)) {
                $outerQuery->where(function ($q) use ($orGroups) {
                    foreach ($orGroups as $field => $terms) {
                        if (is_array($terms)) {
                            foreach ($terms as $term) {
                                $q->orWhere($field, 'LIKE', '%' . $term . '%');
                            }
                        } else {
                            $q->orWhere($field, 'LIKE', '%' . $terms . '%');
                        }
                    }
                });
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

    public function scopeFilterWhen($query, string $filter, $callback): mixed
    {
        $value = \request()->query($filter, null);
        $value = is_null($value) ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $query->when(!is_null($value), function ($query) use ($value, $callback) {
            $callback($query, $value);
        });
    }
}
