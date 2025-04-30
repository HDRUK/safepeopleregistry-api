<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\HmacSigning;
use App\Services\WebhookTranslators\TranslatorFactory;
use App\Http\Traits\Responses;
use Illuminate\Http\JsonResponse;

class VendorWebhookReceiverController extends Controller
{
    use HmacSigning;
    use Responses;

    public function receive(Request $request, string $provider): JsonResponse
    {
        $translator = TranslatorFactory::make($provider);
        if (!$translator->validateSignature($request)) {
            return $this->InvalidSignatureResponse();
        }
        $translatedPayload = $translator->translate(json_decode($request->getContent(), true));
        $translator->saveContext($translatedPayload);

        return $this->OKResponse([]);
    }
}
