<?php

use App\DeploymentSteps\DeploymentStep;
use App\Models\DecisionModel;
use App\Models\State;

/**
 *
 */
return new class () extends DeploymentStep {
    public function handle(): void
    {
        try {
            $STATES = ['organisation_placeholder', 'organisation_invited_by_nonadmin', 'organisation_invited_by_admin'];

            foreach ($STATES as $s) {
                State::firstOrCreate([
                    'name' => Str::studly(strtolower(str_replace('_', ' ', $s))),
                    'slug' => $s,
                ]);
            }

            $this->info("Added new organisation states");
        } catch (\Throwable $e) {
            $this->warn("New organisation states could not be added: {$e->getMessage()}");
        }
    }
};
