<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Traits\ValidationManager;

class GenerateValidationLogs extends Command
{
    use ValidationManager;

    protected $signature = 'validation:generate-logs';

    protected $description = 'Generate validation logs for existing custodians and organisations';

    public function handle()
    {
        $this->info('Generating validation logs...');

        $custodianIds = Custodian::select("id")->pluck("id");
        $organisationIds = Organisation::select("id")->pluck("id");

        foreach ($custodianIds as $custodianId) {
            foreach ($organisationIds as $organisationId) {
                $this->updateCustodianOrganisationValidation(
                    $custodianId,
                    $organisationId
                );
            }
        }

        $this->info('Validation logs generated for all custodian-organisation pairs.');
    }
}
