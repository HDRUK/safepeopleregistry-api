<?php

namespace App\Console\Commands\Oneoff;

use Exception;
use Illuminate\Console\Command;
use App\Models\ValidationCheck;
use Database\Seeders\ValidationCheckSeeder;
use Illuminate\Support\Facades\Log;

class REG2673_20260521_ResetDefaultValidationChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneoff:reset-default-validation-checks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command to reset default validation checks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            ValidationCheck::where('custodian_id', null)->delete();

            $vcs = new ValidationCheckSeeder();
            $vcs->run();

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());


            Log::error('Command adding HR to departments', [
                'message' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }
}
