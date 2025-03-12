<?php

namespace App\Console\Commands;

use App\Jobs\ONSAccreditedResearcherFetch;
use Illuminate\Console\Command;

class onstest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ons-test';

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
        ONSAccreditedResearcherFetch::dispatch();
    }
}
