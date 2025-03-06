<?php

namespace App\Traits;

use App\Models\ValidationLog;
use App\Models\Custodian;
use App\Models\Project;
use App\Models\ProjectHasCustodian;
use App\Models\ProjectHasUser;
use App\Models\Registry;

/**
 * ValidationManager
 *
 *
 */
trait ValidationManager
{
    public function updateCustodianProjectUserValidation(
        int $projectId,
        ?string $userDigitalIdent = null,
        ?int $custodianId = null
    ): void {

        $phus = ProjectHasUser::where('project_id', $projectId)
            ->when($userDigitalIdent, function ($query, $userDigitalIdent) {
                return $query->where('user_digital_ident', $userDigitalIdent);
            })
            ->get();

        $phcs = ProjectHasCustodian::where('project_id', $projectId)
            ->when($custodianId, function ($query, $custodianId) {
                return $query->where('custodian_id', $custodianId);
            })
            ->get();

        foreach ($phus as $phu) {
            $registry = $phu->registry;
            foreach ($phcs as $phc) {
                $custodian = $phc->custodian;
                foreach (ProjectHasUser::getDefaultActions() as $action) {
                    ValidationLog::updateOrCreate(
                        [
                            'entity_id' => $custodian->id,
                            'entity_type' => Custodian::class,
                            'name' => $action,
                            'secondary_entity_id' => $projectId,
                            'secondary_entity_type' => Project::class,
                            'tertiary_entity_id' => $registry->id,
                            'tertiary_entity_type' => Registry::class,
                        ],
                        [
                            'completed_at' => null,
                        ]
                    );
                }
            }
        }
    }

    public function deleteCustodianProjectUserValidation(
        int $projectId,
        ?string $userDigitalIdent = null,
        ?int $custodianId = null
    ): void {

        if (is_null($userDigitalIdent) && is_null($custodianId)) {
            throw new \InvalidArgumentException(
                "You must provide at least userDigitalIdent or custodianId."
            );
        }

        ValidationLog::where('secondary_entity_id', $projectId)
            ->when($userDigitalIdent, function ($query, $udi) {
                $registry = Registry::where('digi_ident', $udi)->first();
                if (! $registry) {
                    throw new \RuntimeException(
                        "Registry not found for digi_ident: {$udi}"
                    );
                }
                return $query->where('tertiary_entity_id', $registry->id);
            }, function ($query) use ($custodianId) {

                return $query->where('entity_id', $custodianId);
            })
            ->delete();
    }
}
