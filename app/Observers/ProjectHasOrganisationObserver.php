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
}
