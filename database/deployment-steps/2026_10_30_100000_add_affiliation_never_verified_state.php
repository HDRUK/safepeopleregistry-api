<?php

use App\DeploymentSteps\DeploymentStep;
use App\Models\State;

/**
 * Adds the state an affiliation ends in when its end date passes before the user ever verified their email.
 */
return new class () extends DeploymentStep {
    public function handle(): void
    {
        try {
            State::firstOrCreate([
                'name' => Str::studly(strtolower(str_replace('_', ' ', State::STATE_AFFILIATION_NEVER_VERIFIED))),
                'slug' => State::STATE_AFFILIATION_NEVER_VERIFIED,
            ]);

            $this->info("Added affiliation never verified state");
        } catch (\Throwable $e) {
            $this->warn("Affiliation never verified state could not be added: {$e->getMessage()}");
        }
    }
};
