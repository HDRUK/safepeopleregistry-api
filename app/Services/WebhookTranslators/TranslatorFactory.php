<?php

namespace App\Services\WebhookTranslators;

class TranslatorFactory
{
    public static function make(?string $provider): WebhookTranslationInterface
    {
        $map = [
            'veriff' => VeriffTranslator::class,
        ];

        if ($provider && isset($map[$provider])) {
            return app($map[$provider]);
        }

        // Fallback to translator for unknown vendors
        return new class () implements WebhookTranslationInterface {
            public function translate(array $data): array
            {
                return [
                    'error' => 'Unknown provider',
                ];
            }

            public function validateSignature($request): bool
            {
                return false;
            }

            public function saveContext(array $data): void
            {
                // No action needed for unknown providers
            }
        };
    }
}
