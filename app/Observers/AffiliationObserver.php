<?php

namespace App\Observers;

use App\Models\Affiliation;
use App\Models\ActionLog;

class AffiliationObserver
{
    /**
     * Handle the Affiliation "created" event.
     */
    public function created(Affiliation $affiliation): void
    {
        /*ActionLog::updateOrCreate(
            ['user_id' => $affiliation->user_id, 'action' => 'affiliations_updated'],
            ['updated_at' => now()]
        );*/
    }

    /**
     * Handle the Affiliation "updated" event.
     */
    public function updated(Affiliation $affiliation): void
    {
        //
    }

    /**
     * Handle the Affiliation "deleted" event.
     */
    public function deleted(Affiliation $affiliation): void
    {
        //
    }

    /**
     * Handle the Affiliation "restored" event.
     */
    public function restored(Affiliation $affiliation): void
    {
        //
    }

    /**
     * Handle the Affiliation "force deleted" event.
     */
    public function forceDeleted(Affiliation $affiliation): void
    {
        //
    }
}
