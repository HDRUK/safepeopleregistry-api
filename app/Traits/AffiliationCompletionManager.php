<?php

namespace App\Traits;

use App\Models\ActionLog;
use App\Models\Affiliation;
use App\Models\User;
use App\Models\Organisation;
use Carbon\Carbon;

trait AffiliationCompletionManager
{
    public function updateActionLog(int $registryId): void
    {
        $registryAffiliations = Affiliation::where('registry_id', $registryId)->get();

        $isComplete = $registryAffiliations->contains(function ($a) {
            return !empty($a->member_id) &&
                !empty($a->relationship) &&
                !empty($a->from);
        });

        $user = User::where("registry_id", $registryId)->first();
        ActionLog::updateOrCreate(
            [
                'entity_id' => $user->id,
                'entity_type' => User::class,
                'action' => User::ACTION_AFFILIATIONS_COMPLETE,
            ],
            ['completed_at' => $isComplete ? Carbon::now() : null]
        );
    }

    private function updateOrganisationActionLog(Affiliation $affiliation): void
    {
        $organisation = $affiliation->organisation;

        if (!$organisation) {
            return;
        }

        $hasAffiliations = Affiliation::where('organisation_id', $organisation->id)
            ->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $organisation->id,
                'entity_type' => Organisation::class,
                'action' => Organisation::ACTION_AFFILIATE_EMPLOYEES_COMPLETED,
            ],
            ['completed_at' => $hasAffiliations ? Carbon::now() : null]
        );
    }
}
