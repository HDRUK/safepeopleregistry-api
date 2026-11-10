<?php

namespace App\Traits;

use App\Models\Custodian;
use App\Models\CustodianWebhookReceiver;
use App\Models\Project;
use App\Models\ProjectHasCustodian;
use App\Models\WebhookEventTrigger;
use Spatie\WebhookServer\WebhookCall;

trait UserLeftProjectWebhook
{
    /**
     * Alerts every Custodian on the project, that has subscribed, that the user has left it,
     * as they may be required to remove the user's data access.
     */
    public function dispatchUserLeftProjectWebhooks(int $projectId, string $digiIdent): void
    {
        $trigger = WebhookEventTrigger::where('name', WebhookEventTrigger::WEBHOOK_EVENT_TRIGGER_USER_LEFT_PROJECT)->first();
        if (!$trigger) {
            return;
        }

        $custodianIds = ProjectHasCustodian::where('project_id', $projectId)
            ->pluck('custodian_id');

        foreach ($custodianIds as $custodianId) {
            $whr = CustodianWebhookReceiver::where([
                'custodian_id' => $custodianId,
            ])->first();

            if ($whr && $trigger->id === $whr->webhook_event) {
                WebhookCall::create()
                    ->url($whr->url)
                    ->payload([
                        'type' => WebhookEventTrigger::WEBHOOK_EVENT_TRIGGER_USER_LEFT_PROJECT,
                        'user' => $digiIdent,
                        'project' => Project::where('id', $projectId)->first()->unique_id,
                    ])
                    // Uses has_hmac with sha256 to encode payload with custodian
                    // unique identifier, thus custodian's known that the payload
                    // hasn't been tampered with in transit.
                    ->useSecret(Custodian::where('id', $custodianId)->first()->unique_identifier)
                    ->dispatch();
            }
        }
    }
}
