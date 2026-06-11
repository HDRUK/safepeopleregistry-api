<?php

namespace App\Console\Commands\Oneoff;

use Exception;
use Illuminate\Console\Command;
use App\Models\ValidationCheck;
use App\Models\ValidationLog;
use Database\Seeders\ValidationCheckSeeder;
use Illuminate\Support\Facades\Log;

class REG2791_20260609_FixValidationLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneoff:fix-validation-logs {--dryrun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command to fix validation logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $dryrun = $this->option('dryrun');
            if ($dryrun) {
                Log::info('Running in dry run mode - no changes will be made to the database.');
             
            }

            $toFix = \DB::select("
                select vl.id, vl.entity_id, vc.id as validation_check_id, vc.name as validation_check_name 
                from validation_logs vl 
                join validation_checks vc on vl.validation_check_id = vc.id 
                where vl.entity_type = 'App\\\\Models\\\\Custodian' and (vl.entity_id != vc.custodian_id or vc.custodian_id is null)
            ");
            
            Log::info(collect($toFix)->toArray());
            $needCorrection = count($toFix);
            Log::info("Found $needCorrection validation logs that need correction.");
            $canCorrect = 0;
            $cannotCorrect = 0;
            foreach ($toFix as $entry) {
                // find the VC that matches the name of the VC associated with this VL, but with the correct custodian_id, and update the VL to use that VC's ID instead
                $correctVC = ValidationCheck::where([
                    'name' => $entry->validation_check_name, 
                    'custodian_id' => $entry->entity_id
                    ])->first();
                if ($correctVC) {
                    if ($dryrun) {
                        Log::info("Would update ValidationLog ID {$entry->id} to use ValidationCheck ID {$correctVC->id}");
                        $canCorrect++;
                    } else {
                        ValidationLog::where('id', $entry->id)->update(['validation_check_id' => $correctVC->id]);
                    }
                } else {
                    Log::warning('No matching ValidationCheck found for log ID ' . $entry->id);
                    $cannotCorrect++;
                }
            }

            Log::info("Summary: $canCorrect logs can be corrected, $cannotCorrect logs cannot be corrected.");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());


            Log::error('Command failed', [
                'message' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }
}
