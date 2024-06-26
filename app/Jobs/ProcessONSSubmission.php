<?php

namespace App\Jobs;

use Carbon\Carbon;

use App\Models\User;
use App\Models\ONSFile;
use App\Models\Training;
use App\Models\Registry;
use App\Models\RegistryHasTraining;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Traits\CommonFunctions;

class ProcessONSSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CommonFunctions;

    private ?ONSFile $file = null;

    /**
     * Create a new job instance.
     */
    public function __construct(ONSFile $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $path = storage_path() . '/app/public/scanned/' . $this->file->path;
        $file = fopen($path, 'r');
        $allData = $this->csvToArray($path);

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
                    'feed_source' => 'ONS',
                    'registry_id' => $registry->id,
                    'user_group' => '',
                    'orc_id' => '',
                ]);

                $awarded = Carbon::parse($row['awarded_at']);
                $expires = Carbon::parse($row['expires_at']);
                $diff = $expires->diffInYears($awarded);

                $training = Training::create([
                    'registry_id' => $registry->id,
                    'provider' => 'ONS',
                    'awarded_at' => $awarded,
                    'expires_at' => $expires,
                    'expires_in_years' => $diff,
                    'training_name' => $row['course_name'],
                ]);

                // Here we would trigger emails to prompt joining the registry on
                // receiving feeds of researchers the system is not aware of.
            }
        }
    }
}
