<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DebugLog;
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
        DebugLog::create([
            'class' => VendorWebhookReceiverController::class,
            'log' => 'Received webhook callback from ' . $provider . ': ' . json_encode($request->json()->all()),
        ]);

        $translator = TranslatorFactory::make($provider);
        if (!$translator->validateSignature($request)) {
            DebugLog::create([
                'class' => VendorWebhookReceiverController::class,
                'log' => 'Unable to validate signature for webhook callback from ' . $provider,
            ]);

            return $this->InvalidSignatureResponse();
        }

        $translatedPayload = $translator->translate(json_decode($request->getContent(), true));
        if (empty($translatedPayload)) {
            DebugLog::create([
                'class' => VendorWebhookReceiverController::class,
                'log' => 'No action to process for webhook callback from ' . $provider,
            ]);

            return $this->OKResponse([]);
        }
        
        $translator->saveContext($translatedPayload);
        return $this->OKResponse([]);
    }
}
