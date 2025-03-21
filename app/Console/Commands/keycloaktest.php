<?php

namespace App\Console\Commands;

use Keycloak;
use Illuminate\Console\Command;

class keycloaktest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:keycloak-test';

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
        dd(Keycloak::checkUserExists(1));
    }
}
