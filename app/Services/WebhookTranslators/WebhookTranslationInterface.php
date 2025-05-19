<?php

namespace App\Services\WebhookTranslators;

use Illuminate\Http\Request;

interface WebhookTranslationInterface
{
    public function validateSignature(Request $request): bool;
    public function translate(array $data): array;
    public function saveContext(array $data): void;
}
