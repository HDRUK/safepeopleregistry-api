<?php

namespace App\Console\Commands;

use OrcID as OID;

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
        OID::token();
    }
}
