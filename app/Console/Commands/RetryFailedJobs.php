<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class RetryFailedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:retry {uuid?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command retry failed jobs - all or by UUID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            return $this->retryAll();
        }

        $uuid = $this->argument('uuid');
        
        if (!$uuid) {
            $this->error('Please provide a UUID or use --all flag');
            return Command::FAILURE;
        }

        return $this->retryByUuid($uuid);
    }

    protected function retryByUuid(string $uuid)
    {
        $this->info("Retrying failed job: {$uuid}");
        
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();
        
        if (is_null($job)) {
            $this->error("Failed job with UUID {$uuid} not found.");
            Log::info("Failed job with UUID {$uuid} not found.");
            return Command::FAILURE;
        }

        $this->retryJob($job);
        $this->info("Job {$uuid} has been retried.");
        
        return Command::SUCCESS;
    }

    protected function retryAll()
    {
        $this->info('Retrying all failed jobs...');
        
        $failedJobs = DB::table('failed_jobs')->get();
        
        if ($failedJobs->isEmpty()) {
            $this->info('No failed jobs to retry.');
            return Command::SUCCESS;
        }

        foreach ($failedJobs as $job) {
            $this->retryJob($job);
        }

        $this->info("Retried {$failedJobs->count()} failed jobs.");
        Log::info("Retried {$failedJobs->count()} failed jobs.");
        
        return Command::SUCCESS;
    }

    protected function retryJob($job)
    {
        Artisan::call('queue:retry', [
            'id' => $job->uuid
        ]);

        Log::info("Retried {$job->uuid} failed job.");
    }
}
