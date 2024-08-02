<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Keycloak;

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
        $credentials = [
            'email' => 'dr@who.com',
            'first_name' => 'Dr',
            'last_name' => 'Who',
            'password' => 'tempP4ssword',
            'is_researcher' => true,
        ];

        Keycloak::createUser($credentials);
    }
}
