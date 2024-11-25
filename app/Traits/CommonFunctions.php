<?php

namespace App\Traits;

use App\Models\SystemConfig;
use Str;

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

    public function mapModelFromString(string $model): string
    {
        $modelName = '\\App\\Models\\'.Str::studly(Str::singular($model));

        return $modelName;
    }

    public function csvToArray(string $filename, $delimiter = ','): array
    {
        if (! file_exists($filename) || ! is_readable($filename)) {
            return [];
        }

        $header = null;
        $data = [];

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (! $header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }

            fclose($handle);
        }

        return $data;
    }
}
