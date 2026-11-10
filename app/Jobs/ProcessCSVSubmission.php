<?php

namespace App\Jobs;

use App\Models\File;
use App\Models\OrganisationHasFile;
use App\Models\User;
use App\Models\State;
use App\Models\Affiliation;
use App\Traits\CommonFunctions;
use App\Enums\AffiliationEndDateSource;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Log;
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
            foreach ($allData as $row) {
                $user = User::where([
                    'first_name' => $row['firstname'],
                    'last_name' => $row['lastname'],
                    'email' => $row['email'],
                ])->first();

                if (!$user) {
                    $row['user_group'] = User::GROUP_USERS;
                    $unclaimedUser = RMC::createUnclaimedUser($row);

                    $endDate = $this->readEndDate($row);

                    $affiliation = Affiliation::create([
                        'organisation_id' => $this->organisationID,
                        'member_id' => '',
                        'relationship' => '',
                        'from' => '',
                        'to' => $endDate,
                        'department' => '',
                        'role' => '',
                        'email' => $row['email'],
                        'ror' => '',
                        'registry_id' => $unclaimedUser->registry_id,
                    ]);
                    if ($endDate !== null) {
                        $affiliation->end_date_recorded_at = Carbon::now();
                        $affiliation->end_date_source = AffiliationEndDateSource::Organisation;
                        $affiliation->save();
                    }

                    $affiliation->setState(State::STATE_AFFILIATION_INVITED);

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

    /**
     * The optional "end_date" column, as 'Y-m-d' or 'dd/mm/yyyy'. A blank, unreadable or past date
     * is left out, so the user is still invited, just without an end date.
     */
    private function readEndDate(array $row): ?string
    {
        $value = trim($row['end_date'] ?? '');
        if ($value === '') {
            return null;
        }

        foreach (['!Y-m-d', '!d/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
            } catch (InvalidFormatException) {
                continue;
            }

            // Rejects overflowing dates, such as 31/02/2026.
            if ($date->format(ltrim($format, '!')) !== $value) {
                continue;
            }

            if ($date->gte(Carbon::today())) {
                return $date->toDateString();
            }
        }

        Log::warning('Bulk invite end date ignored', [
            'organisation_id' => $this->organisationID,
            'end_date' => $value,
        ]);

        return null;
    }
}
