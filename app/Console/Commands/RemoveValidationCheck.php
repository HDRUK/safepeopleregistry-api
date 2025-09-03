<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\CustodianHasValidationCheck;
use App\Models\ValidationCheck;
use Illuminate\Console\Command;
use Spatie\Activitylog\Contracts\Activity;

class RemoveValidationCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-validation-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $array = [
            'no_misconduct',
            'no_relevant_criminal_record',
        ];

        foreach ($array as $check) {
            $id = ValidationCheck::where('name', $check)->value('id');
            if ($id) {
                $this->info("validation check: $check not found in table `validation_checks`");
            }

            ActivityLog::where('log_name', 'validation_check')->where('properties->check_name', $check)->delete();
            CustodianHasValidationCheck::where('validation_check_id', $id)->delete();
            ValidationCheck::where('id', $id)->delete();

            $this->info("Removed validation check: $check");
        }
    }
}
