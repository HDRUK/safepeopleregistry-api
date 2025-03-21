<?php

namespace App\Traits;

use Str;
use App\Models\State;
use App\Models\SystemConfig;
use InvalidArgumentException;

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

    public function generateRorID(): string
    {
        // Crockford base32 alphabet (excluding I, L, O, U)
        $alphabet = '0123456789abcdefghjkmnpqrstuvwxyz';

        // Start with '0'
        $prefix = '0';

        // Generate 6 random Crockford base32 characters
        $randomPart = '';
        for ($i = 0; $i < 6; $i++) {
            $randomPart .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        // Convert the Crockford Base32 to a numeric representation for checksum
        $numericValue = $this->crockfordToNumeric($prefix . $randomPart);

        // Compute ISO/IEC 7064 (mod 97-10) checksum
        $checksum = 98 - bcmod($numericValue . '00', '97');
        $checksum = str_pad((string) $checksum, 2, '0', STR_PAD_LEFT);

        // Construct final ROR ID
        return $prefix . $randomPart . $checksum;
    }

    /**
     * Converts a Crockford Base32 string to a numeric string.
     * This ensures compatibility with bcmod() which requires numeric strings.
     */
    private function crockfordToNumeric(string $input): string
    {
        $alphabet = '0123456789abcdefghjkmnpqrstuvwxyz';
        $map = array_flip(str_split($alphabet));

        $numericValue = '0';
        foreach (str_split(strtolower($input)) as $char) {
            if (!isset($map[$char])) {
                throw new InvalidArgumentException("Invalid character '$char' in Crockford Base32.");
            }
            $numericValue = bcadd(bcmul($numericValue, '32'), (string) $map[$char]);
        }

        return $numericValue;
    }

    public function validateModelStateFilter(array $filter): bool
    {

        foreach ($filter as $f) {
            if (!in_array($f, State::STATES)) {
                return false;
            }
        }

        return true;
    }
}
