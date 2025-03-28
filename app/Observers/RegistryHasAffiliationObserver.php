<?php

namespace App\Observers;

use App\Models\RegistryHasAffiliation;
use App\Models\State;
use App\Traits\AffiliationCompletionManager;

class RegistryHasAffiliationObserver
{
    use AffiliationCompletionManager;
    /**
     * Handle the RegistryHasAffiliation "created" event.
     */
    public function created(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $unclaimed = $registryHasAffiliation->affiliation->organisation->unclaimed;
        $initialState = $unclaimed ? State::STATE_AFFILIATION_INVITED : State::STATE_AFFILIATION_PENDING;
        $registryHasAffiliation->setState($initialState);
        $this->updateActionLog($registryHasAffiliation->registry_id);
        $this->updateOrganisationActionLog($registryHasAffiliation);
    }

    /**
     * Handle the RegistryHasAffiliation "updated" event.
     */
    public function updated(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
        $this->updateOrganisationActionLog($registryHasAffiliation);
    }

    /**
     * Handle the RegistryHasAffiliation "deleted" event.
     */
    public function deleted(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
        $this->updateOrganisationActionLog($registryHasAffiliation);
    }

    /**
     * Handle the RegistryHasAffiliation "restored" event.
     */
    public function restored(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
        $this->updateOrganisationActionLog($registryHasAffiliation);
    }

    /**
     * Handle the RegistryHasAffiliation "force deleted" event.
     */
    public function forceDeleted(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
        $this->updateOrganisationActionLog($registryHasAffiliation);
    }

}
