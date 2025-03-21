<?php

namespace App\Observers;

use App\Models\RegistryHasAffiliation;
use App\Traits\AffiliationCompletionManager;

class RegistryHasAffiliationObserver
{
    use AffiliationCompletionManager;
    /**
     * Handle the RegistryHasAffiliation "created" event.
     */
    public function created(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
    }

    /**
     * Handle the RegistryHasAffiliation "updated" event.
     */
    public function updated(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
    }

    /**
     * Handle the RegistryHasAffiliation "deleted" event.
     */
    public function deleted(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
    }

    /**
     * Handle the RegistryHasAffiliation "restored" event.
     */
    public function restored(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
    }

    /**
     * Handle the RegistryHasAffiliation "force deleted" event.
     */
    public function forceDeleted(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $this->updateActionLog($registryHasAffiliation->registry_id);
    }

}
