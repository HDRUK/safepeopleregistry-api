<?php

namespace App\Traits;

use Str;
use Hash;

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

    public function mapModelFromString(string $model): string
    {
        $modelName = '\\App\\Models\\' . Str::studly(Str::singular($model));
        return $modelName;
    }

    public function generateDigitalIdentifierForRegistry(): string
    {
        $signature = Str::random(64);
        $digiIdent = Hash::make($signature . 
            ':' . env('REGISTRY_SALT_1') .
            ':' . env('REGISTRY_SALT_2')
        );

        return $digiIdent;
    }

    public function csvToArray(string $filename, $delimiter = ','): array
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return [];
        }

        $header = null;
        $data = [];

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (!$header) { 
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
