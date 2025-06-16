<?php

namespace App\Observers;

use App\Models\CustodianHasProjectOrganisation;
use App\Models\CustodianHasProjectUser;
use App\Traits\ValidationManager;

class CustodianHasProjectUserObserver
{
    use ValidationManager;

    /**
     * Handle the CustodianHasProjectUser "created" event.
     */
    public function created(CustodianHasProjectUser $chpu): void
    {
        $custodianId = $chpu->custodian->id;
        $organisationId = $chpu->projectHasUser->affiliation->organisation->id;

        CustodianHasProjectOrganisation::firstOrCreate([
            'custodian_id' => $custodianId
            'organisationId' 
        ]);
    }
}
