<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DuplicateValidationChecksForCustodians extends Command
{
    protected $signature = 'validation-checks:duplicate-for-custodians';

    protected $description = 'Duplicate global validation checks for every custodian and recreate pivot mappings';

    public function handle(): int
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | 1. Get all validation checks where custodian_id IS NULL
            |--------------------------------------------------------------------------
            */
            $globalValidationChecks = DB::table('validation_checks')
                ->whereNull('custodian_id')
                ->get();

            if ($globalValidationChecks->isEmpty()) {
                $this->info('No global validation checks found.');
                return;
            }

            $globalValidationCheckIds = $globalValidationChecks
                ->pluck('id')
                ->toArray();

            $this->info('Found ' . count($globalValidationCheckIds) . ' global validation checks.');

            /*
            |--------------------------------------------------------------------------
            | 2. Delete old pivot records
            |--------------------------------------------------------------------------
            */
            DB::table('custodian_has_validation_check')
                ->whereIn('validation_check_id', $globalValidationCheckIds)
                ->delete();

            $this->info('Deleted old pivot records.');

            /*
            |--------------------------------------------------------------------------
            | 3. Get all custodians
            |--------------------------------------------------------------------------
            */
            $custodians = DB::table('custodians')->get();

            $this->info('Found ' . $custodians->count() . ' custodians.');

            /*
            |--------------------------------------------------------------------------
            | 4. Duplicate validation checks per custodian
            |--------------------------------------------------------------------------
            */
            foreach ($custodians as $custodian) {

                foreach ($globalValidationChecks as $check) {

                    $newCheck = (array) $check;

                    unset($newCheck['id']);

                    $newCheck['custodian_id'] = $custodian->id;


                    $newValidationCheckId = DB::table('validation_checks')
                        ->insertGetId($newCheck);

                    DB::table('custodian_has_validation_check')
                        ->insert([
                            'custodian_id'       => $custodian->id,
                            'validation_check_id'=> $newValidationCheckId,
                        ]);
                }

                $this->info("Processed custodian ID {$custodian->id}");
            }
        });

        $this->info('Completed successfully.');

        return self::SUCCESS;
    }
}