<?php

namespace App\Console\Commands;

use App\Jobs\OrcIDScanner;
use App\Models\User;
use Illuminate\Console\Command;

class orcidtest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:orcid-test';

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
        \Log::info('OrcID scanner - temporarily stopped');
        // OrcIDScanner::dispatch(User::where('id', 10)->first());
    }
}
