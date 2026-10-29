<?php

use App\DeploymentSteps\DeploymentStep;
use Database\Seeders\EmailTemplatesSeeder;

/**
 * Re-runs the email templates seeder to push updated template copy to the database.
 * Truncation is disabled so templates are updateOrCreate'd in place.
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
