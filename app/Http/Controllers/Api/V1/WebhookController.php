<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\DebugLog;
use App\Models\EmailLog;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use Illuminate\Http\JsonResponse;
use App\Models\WebhookEventTrigger;
use App\Http\Controllers\Controller;
use App\Models\CustodianWebhookReceiver;
use App\Http\Requests\Webhooks\GetReceiverByCustodian;
use App\Http\Requests\Webhooks\DeleteReceiverByCustodian;
use App\Http\Requests\Webhooks\UpdateReceiverByCustodian;

/**
 * @OA\Tag(
 *     name="Webhooks",
 *     description="API Endpoints of Webhooks"
 * )
 */
class WebhookController extends Controller
{
    use Responses;

    /**
     * Get all webhook receivers with event trigger details.
     *
     * @OA\Get(
     *     path="/api/v1/webhooks/receivers",
     *     tags={"Webhooks"},
     *     summary="Get all webhook receivers",
     *     description="Returns all webhook receivers with their associated event trigger details",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="custodian_id", type="integer", example=1),
     *                     @OA\Property(property="url", type="string", example="https://example.com/webhook"),
     *                     @OA\Property(property="webhook_event", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-07T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-07T12:00:00Z"),
     *                     @OA\Property(
     *                         property="event_trigger",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="user_created"),
     *                         @OA\Property(property="description", type="string", example="Triggered when a new user is created")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAllReceivers(): JsonResponse
    {
        $receivers = CustodianWebhookReceiver::with('eventTrigger:id,name,description')->get();
        return response()->json([
            'message' => 'success',
            'data' => $receivers
        ]);
    }

    /**
     * Get all webhook receivers for a specific custodian with event trigger details.
     *
     * @OA\Get(
     *     path="/api/v1/webhooks/receivers/{custodianId}",
     *     tags={"Webhooks"},
     *     summary="Get webhook receivers by custodian",
     *     description="Returns all webhook receivers for a specific custodian with their associated event trigger details",
     *     @OA\Parameter(
     *         name="custodianId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="custodian_id", type="integer", example=1),
     *                     @OA\Property(property="url", type="string", example="https://example.com/webhook"),
     *                     @OA\Property(property="webhook_event", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-07T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-07T12:00:00Z"),
     *                     @OA\Property(
     *                         property="event_trigger",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="user_created"),
     *                         @OA\Property(property="description", type="string", example="Triggered when a new user is created")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
     *      )
     * )
     */
    public function getReceiversByCustodian(GetReceiverByCustodian $request, int $custodianId): JsonResponse
    {
        $receivers = CustodianWebhookReceiver::where('custodian_id', $custodianId)
            ->with('eventTrigger:id,name,description')
            ->get();
        return response()->json([
            'message' => 'success',
            'data' => $receivers
        ]);
    }

    /**
     * Create a new webhook receiver.
     *
     * @OA\Post(
     *     path="/api/v1/webhooks/receivers",
     *     tags={"Webhooks"},
     *     summary="Create a new webhook receiver",
     *     description="Creates a new webhook receiver for a custodian",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="custodian_id", type="integer", example=1),
     *             @OA\Property(property="url", type="string", example="https://example.com/webhook"),
     *             @OA\Property(property="webhook_event_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="custodian_id", type="integer", example=1),
     *                 @OA\Property(property="url", type="string", example="https://example.com/webhook"),
     *                 @OA\Property(property="webhook_event", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-07T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-07T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function createReceiver(Request $request): JsonResponse
    {
        $request->validate([
            'custodian_id' => 'required|exists:custodians,id',
            'url' => 'required|url',
            'webhook_event_id' => 'required|exists:webhook_event_triggers,id',
        ]);

        // We don't want these to be duplicated, so either return the existing
        // or create a new receiver.
        $receiver = CustodianWebhookReceiver::firstOrCreate([
            'custodian_id' => $request->custodian_id,
            'url' => $request->url,
            'webhook_event' => $request->webhook_event_id,
        ]);

        return response()->json([
            'message' => 'success',
            'data' => $receiver
        ], 201);
    }
    /**
     * Update a specific webhook receiver.
     *
     * @OA\Put(
     *     path="/api/v1/webhooks/receivers/{custodianId}",
     *     tags={"Webhooks"},
     *     summary="Update a webhook receiver",
     *     description="Updates a specific webhook receiver for a custodian",
     *     @OA\Parameter(
     *         name="custodianId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="url", type="string", example="https://example.com/new-webhook"),
     *             @OA\Property(property="webhook_event_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Webhook receiver not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateReceiver(UpdateReceiverByCustodian $request, int $custodianId): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:custodian_webhook_receivers,id',
            'url' => 'required|url',
            'webhook_event_id' => 'required|exists:webhook_event_triggers,id',
        ]);

        $receiver = CustodianWebhookReceiver::where('custodian_id', $custodianId)
            ->where('id', $request->id)
            ->firstOrFail();

        $receiver->update([
            'url' => $request->url,
            'webhook_event' => $request->webhook_event_id,
        ]);

        return response()->json([
            'message' => 'success',
            'data' => null
        ], 200);
    }

    /**
     * Delete a specific webhook receiver.
     *
     * @OA\Delete(
     *     path="/api/v1/webhooks/receivers/{custodianId}",
     *     tags={"Webhooks"},
     *     summary="Delete a webhook receiver",
     *     description="Deletes a specific webhook receiver for a custodian",
     *     @OA\Parameter(
     *         name="custodianId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Webhook receiver not found"
     *     )
     * )
     */
    public function deleteReceiver(DeleteReceiverByCustodian $request, int $custodianId): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:custodian_webhook_receivers,id',
        ]);
        $receiver = CustodianWebhookReceiver::where('custodian_id', $custodianId)
            ->where('id', $request->id)
            ->firstOrFail();

        $receiver->delete();

        return response()->json([
            'message' => 'success',
            'data' => null
        ], 200);
    }

    /**
     * Get all webhook event triggers.
     *
     * @OA\Get(
     *     path="/api/v1/webhooks/event-triggers",
     *     tags={"Webhooks"},
     *     summary="Get all webhook event triggers",
     *     description="Returns all webhook event triggers",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="user_created"),
     *                     @OA\Property(property="description", type="string", example="Triggered when a new user is created"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-07T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-07T12:00:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAllEventTriggers(Request $request): JsonResponse
    {
        try {
            if ($request->query->count() > 0) {
                return $this->BadRequestResponse();
            }

            $eventTriggers = WebhookEventTrigger::all();
            return response()->json([
                'message' => 'success',
                'data' => $eventTriggers
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get sendgrid webhook event triggers.
     *
     * @OA\Get(
     *     path="/api/v1/webhooks/sendgrid",
     *     tags={"Webhooks"},
     *     summary="Get sendgrid webhook event triggers",
     *     description="Returns sendgrid webhook event triggers",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function sendgrid(Request $request)
    {
        $input = $request->all();

        $lastEvent = $input[0];
        $status = Arr::get($lastEvent, 'event', null);
        $jobUuid = Arr::get($lastEvent, 'job_uuid', null);
        $sgMessageId = Arr::get($lastEvent, 'sg_message_id', null);

        DebugLog::create([
            'class' => __CLASS__,
            'log' => 'SendGrid webhook received: ' . json_encode($input),
        ]);
        
        \Log::info('sendgrid webhook', [
            'input' => $input,
            'event' => $status,
            'job_uuid' => $jobUuid,
            'sg_message_id' => $sgMessageId,
        ]);

        if (is_null($jobUuid)) {
            return response()->json([
                'message' => 'success',
            ]);
        }

        $emailLog = EmailLog::where('job_uuid', $jobUuid)->first();
        if (is_null($emailLog)) {
            return response()->json([
                'message' => 'success',
            ]);
        }

        if ($status === EmailLog::EMAIL_STATUS_DELIVERED) {
            $emailLog->job_status = 1;
            $emailLog->message_status = $status;
            $emailLog->message_response = json_encode($input);
            $emailLog->save();
        } elseif (in_array($status, [
            EmailLog::EMAIL_STATUS_PROCESSED,
            EmailLog::EMAIL_STATUS_DEFERRED,
        ])) {
            $emailLog->message_status = $status;
            $emailLog->message_response = json_encode($input);
            $emailLog->save();
        } else {
            $emailLog->job_status = 0;
            $emailLog->message_status = $status;
            $emailLog->message_response = json_encode($input);
            $emailLog->save();
        }

        return response()->json([
            'message' => 'success',
        ]);
    }
}
