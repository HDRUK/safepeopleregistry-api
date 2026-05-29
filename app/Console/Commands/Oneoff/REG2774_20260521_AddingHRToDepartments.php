<?php

namespace App\Console\Commands\Oneoff;

use Exception;
use Illuminate\Console\Command;
use App\Models\Department;
use App\Models\Organisation;
use Illuminate\Support\Facades\Log;

class REG2774_20260521_AddingHRToDepartments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oneoff:add-hr-to-departments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command adding HR to departments. Not required to be run if DepartmentSeeder has been run';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $dept = Department::updateOrCreate([
                'name' => 'People Services and Human Resources',
                'category' => 'Cross-Cutting Departments',
            ]);

            if ($dept) {
                foreach (Organisation::all() as $org) {
                    $org->departments()->syncWithoutDetaching($dept->id);
                }
            }

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
