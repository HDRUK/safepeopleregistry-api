<?php

namespace App\Http\Traits;

trait HmacSigning
{
    /**
     * Generate a HMAC signature for the given payload using the provided secret key.
     *
     * @param string $payload The payload to sign.
     * @param string $secretKey The secret key to use for signing.
     * @return string The generated HMAC signature.
     */
    public function generateBase64RawSignature(string $payload, string $secretKey): string
    {
        return base64_encode(hash_hmac('sha256', $payload, $secretKey, true));
    }

    public function verifyBase64RawSignature(string $payload, string $secretKey, string $signature): bool
    {
        $expectedSignature = $this->generateBase64RawSignature($payload, $secretKey);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * The string a Custodian signs for a machine-to-machine request. Query parameters are
     * sorted by key and RFC 3986 encoded, so their order in the URL doesn't matter.
     *
     * @param array<string, mixed> $query
     */
    public function buildCanonicalRequest(string $timestamp, string $method, string $path, array $query): string
    {
        ksort($query);

        return implode("\n", [
            $timestamp,
            strtoupper($method),
            $path,
            http_build_query($query, '', '&', PHP_QUERY_RFC3986),
        ]);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function generateCanonicalRequestSignature(
        string $timestamp,
        string $method,
        string $path,
        array $query,
        string $secretKey,
    ): string {
        return $this->generateBase64RawSignature(
            $this->buildCanonicalRequest($timestamp, $method, $path, $query),
            $secretKey
        );
    }

    public function generateSignature(string $payload, string $secretKey): string
    {
        return strtolower(hash_hmac('sha256', $payload, $secretKey));
    }

    public function verifySignature(string $payload, string $secretKey, string $signature): bool
    {
        $expectedSignature = $this->generateSignature($payload, $secretKey);
        return hash_equals($expectedSignature, $signature);
    }
}
