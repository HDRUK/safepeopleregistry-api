<?php

use App\DeploymentSteps\DeploymentStep;
use Spatie\Activitylog\Models\Activity;

/**
 * The leaver flow first logged its activity with the generic 'updated' event. Each now has its own event, so a
 * user's History can say what happened, and a Custodian's confirmation that access was revoked can be found.
 *
 * Without this step, revocations confirmed before this release would be missing from History.
 */
return new class () extends DeploymentStep {
    private const RENAMES = [
        ['custodian_has_project_has_user', ['access revoked'], 'access_revoked'],
        ['affiliation', ['affiliation ended', 'legacy affiliation ended'], 'ended'],
        ['affiliation', ['end date recorded'], 'end_date_recorded'],
    ];

    public function handle(): void
    {
        foreach (self::RENAMES as [$logName, $descriptions, $event]) {
            $renamed = Activity::query()
                ->where('log_name', $logName)
                ->whereIn('description', $descriptions)
                ->where('event', 'updated')
                ->update(['event' => $event]);

            $this->info("Renamed {$renamed} '{$logName}' activities to '{$event}'");
        }
    }
};
