<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Custodian;
use App\Models\RegistryReadRequest;
use App\Notifications\RegistryReadRequestNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\CustodianWebhookReceiver;
use App\Models\WebhookEventTrigger;
use App\Models\DebugLog;
use App\Models\Registry;
use Spatie\WebhookServer\WebhookCall;

class RegistryReadRequestObserver
{
    public const WEBHOOK_EVENT_TRIGGER_NAME = 'registry-read-request';

    /**
     * Handle the RegistryReadRequest "created" event.
     */
    public function created(RegistryReadRequest $registryReadRequest): void
    {
        // Send notification to the Registry owner
        $user = User::where('registry_id', $registryReadRequest->registry_id)->first();
        $custodian = Custodian::where('id', $registryReadRequest->custodian_id)->first();

        Notification::send($user, new RegistryReadRequestNotification($registryReadRequest, $custodian->name));
    }

    /**
     * Handle the RegistryReadRequest "updated" event.
     */
    public function updated(RegistryReadRequest $registryReadRequest): void
    {
        DebugLog::create([
            'class' => RegistryReadRequest::class,
            'log' => WebhookEventTrigger::TRIGGER_EVENT_STATUS[$registryReadRequest->status],
        ]);

        $custodian = Custodian::where('id', $registryReadRequest->custodian_id)->first();
        $wet = WebhookEventTrigger::where([
            'name' => WebhookEventTrigger::TRIGGER_EVENT_STATUS[$registryReadRequest->status],
            'enabled' => 1
        ])->first();

        if (!$wet) {
            return;
        }

        $whr = CustodianWebhookReceiver::where('custodian_id', $custodian->id)
            ->where([
                'webhook_event' => $wet->id,
            ])
            ->first();

        if ($whr) {
            $payload = [
                'action' => WebhookEventTrigger::TRIGGER_EVENT_STATUS[$registryReadRequest->status],
                'digital_identifier' => Registry::where('id', $registryReadRequest->registry_id)->first()->digi_ident,
                'custodian_identifier' => $custodian->unique_identifier,
                'status' => $registryReadRequest->status === RegistryReadRequest::READ_REQUEST_STATUS_APPROVED
                    ? 'approved'
                    : 'rejected',
                'approved_at' => $registryReadRequest->approved_at,
                'rejected_at' => $registryReadRequest->rejected_at,
            ];

            WebhookCall::create()
                ->url($whr->url)
                ->payload($payload)
                ->useSecret($custodian->unique_identifier)
                ->dispatch();

            DebugLog::create([
                'class' => RegistryReadRequest::class,
                'log' => json_encode($whr->toArray()),
            ]);
        }
    }

    /**
     * Handle the RegistryReadRequest "deleted" event.
     */
    public function deleted(RegistryReadRequest $registryReadRequest): void
    {
        // Logic to handle when a registry read request is deleted
    }
}
