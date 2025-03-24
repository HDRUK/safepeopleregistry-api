<?php

namespace App\Observers;

use App\Models\Affiliation;
use App\Models\RegistryHasAffiliation;
use App\Traits\AffiliationCompletionManager;

class AffiliationObserver
{
    use AffiliationCompletionManager;

    public function created(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
    }

    public function updated(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
    }

    public function deleted(Affiliation $affiliation): void
    {
        $this->handleChange($affiliation);
    }

    protected function handleChange(Affiliation $affiliation): void
    {
        $registryIds = RegistryHasAffiliation::where('affiliation_id', $affiliation->id)
            ->distinct()
            ->select('registry_id')
            ->pluck('registry_id');

        foreach ($registryIds as $registryId) {
            $this->updateActionLog($registryId);
        }

    }
}
