<?php

namespace App\Observers;

use App\Models\State;
use App\Traits\ValidationManager;
use App\Models\ProjectHasCustodian;
use App\Models\ProjectHasOrganisation;
use App\Models\CustodianHasProjectOrganisation;

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
            $checking = CustodianHasProjectOrganisation::where([
                'custodian_id' => $custodianId,
                'project_has_organisation_id' => $projectHasOrganisationId
            ])->first();

            if (is_null($checking)) {
                $create = CustodianHasProjectOrganisation::create([
                    'custodian_id' => $custodianId,
                    'project_has_organisation_id' => $projectHasOrganisationId
                ]);

                $custodianHasProjectOrganisation = CustodianHasProjectOrganisation::where('id', $create->id)->first();
                $custodianHasProjectOrganisation->setState(State::STATE_ORG_INVITED);
            }
        }
    }
}
