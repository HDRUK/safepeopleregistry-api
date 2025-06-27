<?php

namespace App\Observers;

use App\Models\ProjectHasCustodian;
use App\Models\Custodian;
use App\Models\ActionLog;
use Carbon\Carbon;
use App\Traits\ValidationManager;

class ProjectHasCustodianObserver
{
    use ValidationManager;
    /**
     * Handle the ProjectHasCustodian "created" event.
     */
    public function created(ProjectHasCustodian $projectHasCustodian): void
    {
        $this->updateActionLog($projectHasCustodian);
        $this->updateCustodianProjectUserValidation(
            $projectHasCustodian->project_id,
            null,
            $projectHasCustodian->custodian_id
        );
    }

    /**
     * Handle the ProjectHasCustodian "updated" event.
     */
    public function updated(ProjectHasCustodian $projectHasCustodian): void
    {
        $this->updateActionLog($projectHasCustodian);
        $this->updateCustodianProjectUserValidation(
            $projectHasCustodian->project_id,
            null,
            $projectHasCustodian->custodian_id
        );
    }

    /**
     * Handle the ProjectHasCustodian "deleted" event.
     */
    public function deleted(ProjectHasCustodian $projectHasCustodian): void
    {
        $this->updateActionLog($projectHasCustodian, true);
        $this->deleteCustodianProjectUserValidation(
            $projectHasCustodian->project_id,
            null,
            $projectHasCustodian->custodian_id
        );
    }

    /**
     * Handle the ProjectHasCustodian "restored" event.
     */
    public function restored(ProjectHasCustodian $projectHasCustodian): void
    {
        $this->updateActionLog($projectHasCustodian);
    }

    /**
     * Handle the ProjectHasCustodian "force deleted" event.
     */
    public function forceDeleted(ProjectHasCustodian $projectHasCustodian): void
    {
        $this->updateActionLog($projectHasCustodian, true);
    }

    private function updateActionLog(ProjectHasCustodian $projectHasCustodian, bool $isDeleting = false): void
    {
        $custodian = $projectHasCustodian->custodian;
        $hasProjects = $custodian->projects()
            ->when($isDeleting, function ($query) use ($projectHasCustodian) {
                return $query->where(
                    'project_id',
                    '!=',
                    $projectHasCustodian->project_id
                );
            })
            ->exists();

        ActionLog::updateOrCreate(
            [
                'entity_id' => $custodian->id,
                'entity_type' => Custodian::class,
                'action' => Custodian::ACTION_ADD_PROJECTS,
            ],
            ['completed_at' => $hasProjects ? Carbon::now() : null]
        );
    }
}
