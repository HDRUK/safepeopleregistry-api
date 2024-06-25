<?php

namespace App\Traits;

use App\Models\SystemConfig;

trait CommonFunctions
{
    public function getSystemConfig(string $name): ?string
    {
        $systemConfig = SystemConfig::where('name', $name)->first();
        if ($systemConfig) {
            return $systemConfig->value;
        }

        return null;
    }
}