<?php

use App\DeploymentSteps\DeploymentStep;
use App\Models\Affiliation;
use App\Services\AffiliationLeaverService;
use Illuminate\Support\Facades\Log;

/**
 * Before the leaver flow, users could add historic affiliations with a past end date, left pending or awaiting
 * email verification. Without this step, the first scheduled checking_affiliations run would end them all and
 * email users, Organisations and Custodians about leavers who may have left years ago.
 *
 * They are ended quietly instead: their projects are left, but nobody is notified, no webhooks are sent and
 * Custodians are not asked to revoke access. Must run before the checking_affiliations schedule is enabled.
 */
return new class () extends DeploymentStep {
    public function handle(): void
    {
        $leaver = app(AffiliationLeaverService::class);
        $ended = 0;
        $failures = 0;

        Affiliation::query()->dueToEnd()->chunkById(100, function ($affiliations) use ($leaver, &$ended, &$failures) {
            foreach ($affiliations as $affiliation) {
                try {
                    if ($leaver->endLegacy($affiliation)) {
                        $ended++;
                    }
                } catch (\Throwable $e) {
                    $failures++;
                    $this->warn("Legacy affiliation {$affiliation->id} could not be ended: {$e->getMessage()}");
                    Log::error('Deployment step ending legacy affiliations', [
                        'id' => $affiliation->id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Ended {$ended} legacy affiliations quietly");

        if ($failures > 0) {
            $this->warn("{$failures} legacy affiliations could not be ended, and will be ended by the scheduler with notifications");
        }
    }
};
