<?php

namespace App\Observers;

use App\Models\CustodianHasProjectOrganisation;
use App\Models\ProjectHasOrganisation;
use App\Models\ProjectHasCustodian;
use App\Traits\ValidationManager;

class ProjectHasOrganisationObserver
{
    use ValidationManager;

    /**
     * Handle the CustodianHasProjectUser "created" event.
     */
    public function created(ProjectHasOrganisation $pho): void
    {
        $projectHasOrganisationId = $pho->id;
        $custodianIds = ProjectHasCustodian::where(['project_id' => $pho->project_id])->pluck("custodian_id")->toArray();

        foreach ($custodianIds as $custodianId) {
            CustodianHasProjectOrganisation::firstOrCreate([
                'custodian_id' => $custodianId,
                'project_has_organisation_id' => $projectHasOrganisationId
            ]);
        }
    }

    /**
     * Handle the ProjectHasOrganisation "deleted" event.
     */
    public function deleted(ProjectHasOrganisation $projectHasOrganisation): void
    {
        //*** This doesn't feel right -> There could be multiple approvals for the same organisation from each custodian but it is like this in users ***
        $custodianApproval = ProjectHasCustodian::where('project_id', $projectHasOrganisation->project_id)
            ->where("approved", true)
            ->first();
        $whr = CustodianWebhookReceiver::where([
            'custodian_id' => $custodianApproval->custodian_id,
        ])->first();

        $this->deleteCustodianProjectOrganisationValidation(
            $projectHasOrganisation->project_id,
            $custodianApproval->custodian_id,
            $projectHasOrganisation->organisation_id
        );

        if ($whr) {
            if (WebhookEventTrigger::where('name', 'organisation-left-project')->first()->id === $whr->webhook_event) {
                WebhookCall::create()
                    ->url($whr->url)
                    ->payload([
                        'type' => 'organisation-left-project',
                        'organisation' => $organisation->id,
                        'project' => Project::where('id', $projectHasOrganisation->project_id)->first()->unique_id,
                    ])
                    ->useSecret(Custodian::where('id', $custodianApproval->custodian_id)->first()->unique_identifier)
                    ->dispatch();
            }
        }
    }
}
