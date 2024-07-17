<?php

namespace App\Jobs;

use TriggerEmail;

use Carbon\Carbon;

use App\Models\User;
use App\Models\File;
use App\Models\Training;
use App\Models\Registry;
use App\Models\OrganisationHasFile;
use App\Models\RegistryHasTraining;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Traits\CommonFunctions;

class ProcessCSVSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CommonFunctions;

    private ?File $file = null;
    private int $organisationID = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(File $file, int $organisationID)
    {
        $this->file = $file;
        $this->organisationID = $organisationID;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = storage_path() . '/app/public/scanned/' . $this->file->path;
        $file = fopen($path, 'r');
        $allData = $this->csvToArray($path);
        fclose($file);

        foreach ($allData as $row) {
            $user = User::where([
                'first_name' => $row['firstname'],
                'last_name' => $row['lastname'],
                'email' => $row['email'],
            ])->first();

            if (!$user) {
                $registry = Registry::create([
                    'dl_ident' => null,
                    'pp_ident' => null,
                    'digi_ident' => $this->generateDigitalIdentifierForRegistry(),
                    'verified' => 0,
                ]);

                // Create unclaimed account
                $user = User::create([
                    'first_name' => $row['firstname'],
                    'last_name' => $row['lastname'],
                    'email' => $row['email'],
                    'unclaimed' => 1,
                    'feed_source' => 'ORG',
                    'registry_id' => $registry->id,
                    'user_group' => '',
                    'orc_id' => '',
                ]);

                $input = [
                    'type' => 'USER',
                    'to' => $user->id,
                    'by' => $this->organisationID,
                    'identifier' => 'delegate_sponsor',
                ];

                TriggerEmail::spawnEmail($input);
            
                if (is_file($path) && @unlink($path)) {
                    OrganisationHasFile::where([
                        'file_id' => $this->file->id,
                        'organisation_id' => $this->organisationID,
                    ])->delete();
                    
                    $this->file->delete();
                }
            }
        }
    }
}
