<?php

namespace App\Traits;

use App\Models\RegistryHasAffiliation;
use App\Models\ActionLog;
use App\Models\User;
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
}
