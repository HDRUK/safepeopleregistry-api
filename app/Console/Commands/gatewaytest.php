<?php

namespace App\Console\Commands;

use Gateway;
use Illuminate\Console\Command;

class gatewaytest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:gateway-test';

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
        dd(Gateway::getDataUsesByProjectID(1, 1));
    }
}
