<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Registry;
use Illuminate\Console\Command;

class RemoveRegistryForCustodians extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-registry-for-custodians';

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
        $custodians = User::where('user_group', 'CUSTODIANS')->get();

        foreach ($custodians as $custodian) {
            Registry::where('id', $custodian->registry_id)->delete();

            $custodian->registry_id = null;
            $custodian->save();
        }

        $this->info('Registry associations removed for custodians.');
    }
}
