<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Registry;
use Illuminate\Console\Command;

class RemoveRegistryForOrganisations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-registry-for-organisations';

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
        $organisations = User::where('user_group', 'ORGANISATIONS')->get();

        foreach ($organisations as $organisation) {
            Registry::where('id', $organisation->registry_id)->delete();

            User::where([
                'id' => $organisation->id,
            ])->update([
                'registry_id' => null,
            ]);
        }

        $this->info('Registry associations removed for organisations.');
    }
}
