<?php

namespace App\Console\Commands;

use App\Jobs\MergeUserAccounts;
use App\Models\Affiliation;
use Illuminate\Console\Command;

class TestJobCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-test-job';

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
        $aff = Affiliation::where("email", "calum.macdonald@ed.ac.uk")->orderBy('id', 'desc')->first();
        MergeUserAccounts::dispatch($aff);
    }
}
