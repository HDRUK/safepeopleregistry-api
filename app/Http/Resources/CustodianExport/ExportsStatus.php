<?php

namespace App\Http\Resources\CustodianExport;

use App\Models\ModelState;

trait ExportsStatus
{
    /**
     * @return array{slug: string, name: string}|null
     */
    protected static function status(?ModelState $modelState): ?array
    {
        $state = $modelState?->state;

        return $state ? ['slug' => $state->slug, 'name' => $state->name] : null;
    }
}
