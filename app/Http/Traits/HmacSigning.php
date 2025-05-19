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
