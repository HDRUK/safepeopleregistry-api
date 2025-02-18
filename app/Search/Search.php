<?php

namespace App\Search;

use Illuminate\Http\Request;

class Search
{
    public static function sanitiseFilters(Request $request, string $key)
    {
        $value = $request->query($key, null);

        return is_null($value) ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
