<?php

namespace App\Observers;

use App\Models\ProjectHasCustodian;
use App\Models\Custodian;
use App\Models\ActionLog;
use Carbon\Carbon;

class ProjectHasCustodianObserver
{
    /**
     * Handle the ProjectHasCustodian "created" event.
     */
    public function created(ProjectHasCustodian $projectHasCustodian): void
    {
        $this->updateActionLog($projectHasCustodian);
    }

    /**
     * Handle the ProjectHasCustodian "updated" event.
     */
    public function updated(ProjectHasCustodian $projectHasCustodian): void
    {
        $this->updateActionLog($projectHasCustodian);
    }

    /**
     * Handle the ProjectHasCustodian "deleted" event.
     */
    public function deleted(ProjectHasCustodian $projectHasCustodian): void
    {
        $this->updateActionLog($projectHasCustodian, true);
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
