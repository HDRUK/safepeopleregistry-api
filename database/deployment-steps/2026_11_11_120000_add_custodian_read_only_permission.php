<?php

use App\DeploymentSteps\DeploymentStep;
use App\Enums\CustodianRole;
use App\Models\Permission;

/**
 * Adds the CUSTODIAN_READ_ONLY role. PermissionSeeder only runs on fresh environments,
 * so existing ones need the row creating here. Safe to run more than once.
 */
return new class () extends DeploymentStep {
    public function handle(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => CustodianRole::ReadOnly->value],
            ['enabled' => true, 'description' => 'read only'],
        );

        $this->info(
            $permission->wasRecentlyCreated
                ? "Created permission '{$permission->name}'"
                : "Permission '{$permission->name}' already exists"
        );
    }
};
