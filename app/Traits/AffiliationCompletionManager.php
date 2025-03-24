<?php

namespace App\Traits;

use App\Models\RegistryHasAffiliation;
use App\Models\ActionLog;
use App\Models\User;
use App\Models\Organisation;
use Carbon\Carbon;

trait AffiliationCompletionManager
{
    public function updateActionLog(int $registryId): void
    {
        $registryAffiliations = RegistryHasAffiliation::with('affiliation')
            ->where('registry_id', $registryId)
            ->get();

        $isComplete = $registryAffiliations->contains(function ($rha) {
            $a = $rha->affiliation;
            return $a &&
                !empty($a->member_id) &&
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

    private function updateOrganisationActionLog(RegistryHasAffiliation $registryHasAffiliation): void
    {
        $affiliation = $registryHasAffiliation->affiliation;
        $organisation = $affiliation?->organisation;

        if (!$organisation) {
            return;
        }

        $hasAssociations = RegistryHasAffiliation::whereHas(
            'affiliation',
            function ($query) use ($organisation) {
                $query->where('organisation_id', $organisation->id);
            }
        )->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $organisation->id,
                'entity_type' => Organisation::class,
                'action' => Organisation::ACTION_AFFILIATE_EMPLOYEES_COMPLETED,
            ],
            ['completed_at' => $hasAssociations ? Carbon::now() : null]
        );
    }


}
