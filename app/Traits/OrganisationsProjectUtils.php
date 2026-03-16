<?php

namespace App\Traits;

use App\Models\CustodianHasProjectOrganisation;
use App\Models\Organisation;

trait OrganisationsProjectUtils
{
    public function updateAllCustodianHasProjectOrganisationStates(Organisation $org, string $state)
    {
      $custodianHasProjectOrganisations = CustodianHasProjectOrganisation::query()
            ->whereHas('projectOrganisation', function ($query) use ($org) {
                $query->where('organisation_id', $org->id);
            })->get();

        foreach ($custodianHasProjectOrganisations as $custodianHasProjectOrganisation) {
            $custodianHasProjectOrganisation->setState($state);
        }  
    }
}