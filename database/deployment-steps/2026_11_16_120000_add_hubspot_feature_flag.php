<?php

use App\DeploymentSteps\DeploymentStep;
use Illuminate\Support\Facades\DB;

return new class () extends DeploymentStep {
    public function handle(): void
    {
        try {
            DB::table('features')->updateOrInsert(
                ['name' => 'HubspotEnabled', 'scope' => '__laravel_null'],
                [
                    'value' => 'false',
                    'description' => 'Enable Hubspot integration. This overrides the HUBSPOT_INTEGRATION_ENABLED environment variable.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
            $this->info("Added new feature flag HubspotEnabled");
        } catch (\Throwable $e) {
            $this->warn("New HubspotEnabled feature flag could not be added: {$e->getMessage()}");
        }
    }
};
