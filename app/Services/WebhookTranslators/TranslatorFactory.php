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
        };
    }
}
