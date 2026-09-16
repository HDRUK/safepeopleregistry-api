<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\OrganisationHasFile;
use App\Models\User;
use App\Traits\CommonFunctions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use TriggerEmail;
use RegistryManagementController as RMC;

class ProcessCSVSubmission implements ShouldQueue
{
    use CommonFunctions;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
        $disk = Storage::disk(config('speedi.system.scanning_filesystem_disk') . '_scanned');
        $tmpPath = tempnam(sys_get_temp_dir(), 'researcher_list_');
        $source = $disk->readStream($this->file->path);
        $target = fopen($tmpPath, 'w');
        stream_copy_to_stream($source, $target);
        fclose($target);
        fclose($source);

        try {
            $allData = csvToArray($tmpPath);
            \Log::info('CSV Data: ' . json_encode($allData));
            foreach ($allData as $row) {
                $user = User::where([
                    'first_name' => $row['firstname'],
                    'last_name' => $row['lastname'],
                    'email' => $row['email'],
                ])->first();

                if (!$user) {
                    $row['user_group'] = User::GROUP_USERS;
                    $unclaimedUser = RMC::createUnclaimedUser($row);

                    $input = [
                        'type' => 'USER',
                        'to' => $unclaimedUser->id,
                        'by' => $this->organisationID,
                        'identifier' => 'organisation_user_invite',
                    ];

                    TriggerEmail::spawnEmail($input);
                }
            }
        } finally {
            if (is_file($tmpPath) && @unlink($tmpPath)) {
                OrganisationHasFile::where([
                    'file_id' => $this->file->id,
                    'organisation_id' => $this->organisationID,
                ])->delete();

                $disk->delete($this->file->path);
            }
        }
    }
}
