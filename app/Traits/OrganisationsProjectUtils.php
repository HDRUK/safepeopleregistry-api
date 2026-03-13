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
            \Log::info('<<<<<<<$custodianHasProjectOrganisations');

        foreach ($custodianHasProjectOrganisations as $custodianHasProjectOrganisation) {
                $currentState = $custodianHasProjectOrganisation->getState();

                $canIBeACustard = $custodianHasProjectOrganisation->canTransitionTo($state);
                \Log::info('<<<<<<<$sometimesIEatSundayRoasts '.$canIBeACustard);
                \Log::info('<<<<<<<$currentState '.$currentState);
                \Log::info('<<<<<<<$state '.$state);



            if ($custodianHasProjectOrganisation->getState() !== $state) {
                $custodianHasProjectOrganisation->setState($state);
            }
            
        }  
    }
}