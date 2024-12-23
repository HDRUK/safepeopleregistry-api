<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Registry;
use App\Models\Project;
use App\Models\Custodian;
use App\Models\ProjectHasUser;
use App\Models\WebhookEventTrigger;
use App\Models\ProjectHasCustodianApproval;
use App\Models\CustodianWebhookReceiver;
use Spatie\WebhookServer\WebhookCall;

class ProjectHasUserObserver
{
    public const WEBHOOK_EVENT_TRIGGER_NAME = 'user-left-project';

    /**
     * Handle the ProjectHasUser "created" event.
     */
    public function created(ProjectHasUser $projectHasUser): void
    {
        //
    }

    /**
     * Handle the ProjectHasUser "updated" event.
     */
    public function updated(ProjectHasUser $projectHasUser): void
    {
        //
    }

    /**
     * Handle the ProjectHasUser "deleted" event.
     */
    public function deleted(ProjectHasUser $projectHasUser): void
    {
        // In this instance, we're intercepting the delete event of a user
        // being removed from a project. As such, when this happens, and
        // provided this project has custodian approval, we fire a
        // webhook to alert custodian's that they may be required
        // to remove data access for said user.
        $registry = Registry::where('digi_ident', $projectHasUser->user_digital_ident)->first();
        $user = User::where('registry_id', $registry->id)->first();

        $custodianApproval = ProjectHasCustodianApproval::where('project_id', $projectHasUser->project_id)->first();
        $whr = CustodianWebhookReceiver::where([
            'custodian_id' => $custodianApproval->custodian_id,
        ])->first();

        if ($whr) {
            if (WebhookEventTrigger::where('name', 'user-left-project')->first()->id === $whr->webhook_event) {
                WebhookCall::create()
                    ->url($whr->url)
                    ->payload([
                        'user' => $user->registry->digi_ident,
                        'project' => Project::where('id', $projectHasUser->project_id)->first()->unique_id,
                    ])
                    // Uses has_hmac with sha256 to encode payload with custodian
                    // unique identifier, thus custodian's known that the payload
                    // hasn't been tampered with in transit.
                    ->useSecret(Custodian::where('id', $custodianApproval->custodian_id)->first()->unique_identifier)
                    ->dispatch();
            }
        }
    }

    /**
     * Handle the ProjectHasUser "restored" event.
     */
    public function restored(ProjectHasUser $projectHasUser): void
    {
        //
    }

    /**
     * Handle the ProjectHasUser "force deleted" event.
     */
    public function forceDeleted(ProjectHasUser $projectHasUser): void
    {
        //
    }
}
