<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DebugLog;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Http\Traits\HmacSigning;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\WebhookTranslators\TranslatorFactory;
use App\Http\Requests\VendorWebhookReceivers\GetVendorWebhookReceiverByProvider;

/**
 * @OA\Tag(
 *     name="VendorWebhookReceiver",
 *     description="API endpoints for receiving vendor webhook callbacks"
 * )
 */
class VendorWebhookReceiverController extends Controller
{
    use HmacSigning;
    use Responses;

    /**
     * @OA\Post(
     *     path="/api/v1/vendor-webhooks/{provider}",
     *     tags={"VendorWebhookReceiver"},
     *     summary="Receive a webhook callback from a vendor",
     *     @OA\Parameter(
     *         name="provider",
     *         in="path",
     *         required=true,
     *         description="Name of the vendor providing the webhook",
     *         @OA\Schema(type="string", example="example-provider")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             example={"event": "user.created", "data": {"id": 123, "name": "John Doe"}}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *         )
     *     ),
     *    @OA\Response(
     *        response=500,
     *        description="Internal server error",
     *        @OA\JsonContent(
     *            type="object",
     *            @OA\Property(property="message", type="string", example="An error occurred")
     *        )
     *    )
     * )
     */
    public function receive(GetVendorWebhookReceiverByProvider $request, string $provider): JsonResponse
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
