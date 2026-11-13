<?php

use App\DeploymentSteps\DeploymentStep;
use Database\Seeders\EmailTemplatesSeeder;

/**
 * Re-runs the email templates seeder to add the custodian notification templates:
 * custodian_invite_read_only, affiliation_ended_custodian_read_only and project_user_added_custodian.
 * With DISABLE_TRUNCATE set, templates are updateOrCreate'd in place; without it, the seeder empties
 * the table and recreates every template.
 */
return new class () extends DeploymentStep {
    public function handle(): void
    {
        try {
            $this->call('db:seed', [
                '--class' => EmailTemplatesSeeder::class,
                '--force' => true,
            ]);

            $this->info("Seeded email templates");
        } catch (\Throwable $e) {
            $this->warn("Email templates could not be seeded: {$e->getMessage()}");
        };
    }
};
