<?php

use Carbon\Carbon;

if (!function_exists('convertStates')) {
    function convertStates($state)
    {
        return strtoupper(str_replace('_', ' ', $state));
    }
}

if (!function_exists('csvToArray')) {
    function csvToArray(string $filename, $delimiter = ','): array
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

if (!function_exists('generateRorID')) {
    function generateRorID(): string
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
        $numericValue = crockfordToNumeric($prefix . $randomPart);

        // Compute ISO/IEC 7064 (mod 97-10) checksum
        $checksum = 98 - bcmod($numericValue . '00', '97');
        $checksum = str_pad((string) $checksum, 2, '0', STR_PAD_LEFT);

        // Construct final ROR ID
        return $prefix . $randomPart . $checksum;
    }
}

/**
 * Converts a Crockford Base32 string to a numeric string.
 * This ensures compatibility with bcmod() which requires numeric strings.
 */
if (!function_exists('crockfordToNumeric')) {
    function crockfordToNumeric(string $input): string
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
}

if (!function_exists('isValidDate')) {
    /**
     * Check if a string is a valid date in the specified format.
     *
     * @param string $date The date string to validate
     * @param string $format The expected date format (default: 'Y-m-d')
     * @return bool True if the date is valid, false otherwise
     *
     * @example
     * isValidDate('2026-01-14'); // returns true
     * isValidDate('2026-13-45'); // returns false
     * isValidDate('14/01/2026', 'd/m/Y'); // returns true
     */
    function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        return Carbon::canBeCreatedFromFormat($date, $format);
    }
}

if (!function_exists('getHeaderValue')) {
    /**
     * Extract the value from an array of HTTP headers by searching for a specific header name.
     * 
     * Searches through an array of header strings (format: "Header-Name: value") and returns
     * the value portion when the header name matches the search value. The search is case-insensitive.
     *
     * @param array $headers Array of header strings in "name: value" format
     * @param string $searchValue The header name to search for (e.g., 'X-Message-Id')
     * @return string|null The header value if found, null otherwise
     * 
     * @example
     * $headers = ['Content-Type: application/json', 'X-Message-Id: abc123'];
     * getHeaderValue($headers, 'x-message-id'); // Returns: 'abc123'
     */
    function getHeaderValue(array $headers, string $searchValue)
    {
        $response = null;

        foreach ($headers as $value) {
            if (str_starts_with(strtolower($value), strtolower($searchValue))) {
                $parts = explode(':', $value, 2);
                if (count($parts) === 2) {
                    $response = trim($parts[1]);
                    break;
                }
            }
        }

        return $response;
    }
}