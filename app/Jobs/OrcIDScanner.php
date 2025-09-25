<?php

namespace App\Jobs;

use OrcID;
use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Education;
use App\Models\Affiliation;
use Illuminate\Support\Arr;
use App\Models\Organisation;
use App\Models\Accreditation;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Models\RegistryHasAccreditation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class OrcIDScanner implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $user = null;

    private $accessToken = null;

    // /**
    //  * Create a new job instance.
    //  */
    public function __construct($userId)
    {
        $this->user = User::find($userId);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if ($this->attempts() > 3) {
            $this->sendLog('OrcID Scanner failed: max attempts reached; deleting job', 'orcid_scan.job_removed', 'max_attempts_reached', 'deleted');
            $this->delete();
            return;
        }

        if (blank($this->user->orc_id)) {
            $this->sendLog('OrcID Scanner failed: user has no ORCID; deleting job', 'orcid_scan.job_removed', 'user_orcid_blank', 'deleted');
            $this->delete();
            return;
        }

        if ($this->user->orcid_scanning === 1) {
            $this->sendLog("OrcID Scanner failed - duplicate ORCID scan detected; deleting job.", 'orcid_scan.duplicate_job', 'already_running_or_already_has_orcid', 'deleted');
            $this->delete();
            return;
        }

        $this->release(delay: now()->addSeconds(10 * ($this->attempts() + 1)));

        Log::info('OrcIDScanner: OrcID scanning started.');
        return 0;

        try {
            if ($this->user->consent_scrape && $this->user->orc_id !== null && $this->user->user_group === User::GROUP_USERS) {
                $this->user->orcid_scanning = 1;
                $this->user->save();

                $token = json_decode(OrcID::getPublicToken($this->user), true);

                $this->accessToken = '';
                if (isset($token['access_token'])) {
                    $this->accessToken = Arr::get($token, 'access_token', '');
                } else {
                    Log::error('OrcID token is empty', ['user_id' => $this->user->id]);
                }

                $this->getEducations();
                $this->getQualifications();
                $this->getEmployers();

                $this->user->orcid_scanning = 0;
                $this->user->orcid_scanning_completed_at = Carbon::now();
                $this->user->save();
            }
        } catch (Throwable $e) {
            Log::error('OrcID Scanner failed', [
                'message' => $e->getMessage(),
            ]);
        }

        return 0;
    }

    public function sendLog($message, $event, $reason, $decision): void
    {
        $log = [
            'event'      => $event,
            'job_class'  => static::class,
            'job_id'     => $this->job?->getJobId(),
            'queue'      => $this->job?->getQueue(),
            'attempt'    => $this->attempts(),
            'max_tries'  => 3,
            'user_id'    => $this->user->id,
            'orcid_scanning' => $this->user->orcid_scanning ? true : false,
            'orcid_present' => blank($this->user->orc_id) ? false : true,
            'reason'     => $reason,
            'decision'   => $decision,
        ];

        Log::error($message, $log);
    }

    public function failed(Throwable $exception): void
    {
        Log::info('OrcID Scanner failed', [
            'user_id' => $this->user->id,
            'class_exception' => get_class($exception),
            'message' => $exception->getMessage(),
        ]);
    }

    private function getEducations(): void
    {
        $record = OrcID::getOrcIDRecord($this->accessToken, $this->user->orc_id, 'educations');
        $affiliationsGroup = isset($record['affiliation-group']) ? $record['affiliation-group'] : [];
        foreach ($affiliationsGroup as $affiliations) {
            $affiliationsSummaries = isset($affiliations['summaries']) ? $affiliations['summaries'] : [];
            foreach ($affiliationsSummaries as $summary) {
                $education = isset($summary['education-summary']) ? $summary['education-summary'] : [];
                $title = isset($education['role-title']) ? $education['role-title'] : '';
                $organisation = isset($education['organization']) ? $education['organization'] : [];

                $dates = [
                    'startDate' => $this->normaliseDate($education['start-date']),
                    'endDate' => $this->normaliseDate($education['end-date']),
                ];

                Education::firstOrCreate([
                    'title' => $title,
                    'from' => $dates['startDate'],
                    'to' => $dates['endDate'],
                    'institute_name' => Arr::get($organisation, 'name', ''),
                    'institute_address' => json_encode(Arr::get($organisation, 'address', [])),
                    'institute_identifier' => Arr::get($organisation, 'disambiguated-organization.disambiguated-organization-identifier', ''),
                    'source' => Arr::get($organisation, 'disambiguated-organization.disambiguation-source', ''),
                    'registry_id' => $this->user->registry_id,
                ]);
            }
        }
    }

    private function getQualifications(): void
    {
        $record = OrcID::getOrcIDRecord($this->accessToken, $this->user->orc_id, 'qualifications');

        $affiliationsGroup = isset($record['affiliation-group']) ? $record['affiliation-group'] : [];
        foreach ($affiliationsGroup as $affiliations) {
            $affiliationsSummaries = isset($affiliations['summaries']) ? $affiliations['summaries'] : [];
            foreach ($affiliationsSummaries as $summary) {

                $qualification = isset($summary['qualification-summary']) ? $summary['qualification-summary'] : [];
                $title = Arr::get($qualification, 'role-title', '');
                $organisation = Arr::get($qualification, 'organization', []);

                $dates = [
                    'startDate' => $this->normaliseDate($qualification['start-date']),
                    'endDate' => $this->normaliseDate($qualification['end-date']),
                ];

                // TODO - This is a refactor candidate, as we can't firstOrCreate this as there
                // is no real unique constraints on the table. We need to hard link this to
                // registry_id instead. Future scope, as this is a larger change impacting several
                // areas of the code.
                $accreditation = Accreditation::create([
                    'awarded_at' => $dates['startDate'],
                    'awarding_body_name' => Arr::get($organisation, 'name', ''),
                    'awarding_body_ror' => Arr::get($organisation, 'disambiguated-organization.disambiguated-organization-identifier', ''),
                    'title' => $title,
                    'expires_at' => $dates['endDate'],
                    'awarded_locale' => Arr::get($organisation, 'address.country', ''),
                ]);

                RegistryHasAccreditation::firstOrCreate([
                    'registry_id' => $this->user->registry_id,
                    'accreditation_id' => $accreditation->id,
                ]);
            }
        }
    }

    private function getEmployers(): void
    {
        $record = OrcID::getOrcIDRecord($this->accessToken, $this->user->orc_id, 'employments');

        $affiliationsGroup = isset($record['affiliation-group']) ? $record['affiliation-group'] : [];
        foreach ($affiliationsGroup as $affiliations) {
            $affiliations = isset($affiliations['summaries']) ? $affiliations['summaries'] : [];
            foreach ($affiliations as $summary) {
                $organisation = isset($summary['employment-summary']) ? $summary['employment-summary'] : [];

                $knownOrg = $this->isAKnownOrganisation(Arr::get($organisation, 'organization.name', ''));

                $dates = [
                    'startDate' => $this->normaliseDate($organisation['start-date']),
                    'endDate' => $this->normaliseDate($organisation['end-date']),
                ];

                $preExistingAffiliation = Affiliation::where([
                    'department' => Arr::get($organisation, 'department-name', ''),
                    'from' => $dates['startDate'],
                    'to' => $dates['endDate'],
                    'role' => Arr::get($organisation, 'role-title', ''),
                    'registry_id' => $this->user->registry_id,
                ])->first();

                // We don't want to duplicate information, so skip this as we've already
                // reported this role, within this time frame, for this registry_id, as
                // it is likely a dupe.
                if ($preExistingAffiliation) {
                    continue;
                }

                Affiliation::create([
                    'from' => $dates['startDate'],
                    'to' => $dates['endDate'],
                    'is_current' => ($dates['endDate'] === '') ? 1 : 0,
                    'department' => Arr::get($organisation, 'department-name', ''),
                    'role' => Arr::get($organisation, 'role-title', ''),
                    'employer_address' => json_encode(Arr::get($organisation, 'organization.address', [])),
                    'ror' => Arr::get($organisation, 'organization.disambiguated-organization.disambiguated-organization-identifier', ''),
                    'registry_id' => $this->user->registry_id,
                    'organisation_id' => $knownOrg->id ?? -1,
                    'member_id' => '',
                ]);
            }
        }
    }

    private function isAKnownOrganisation(string $name): ?Organisation
    {
        $org = Organisation::where('organisation_name', 'like', '%' . $name . '%')->first();
        if ($org) {
            return $org;
        }

        return null;
    }

    private function normaliseDate(?array $date): string
    {
        $formedDateString = '';

        if (is_array($date)) {
            if (isset($date['day'])) {
                $formedDateString .= Arr::get($date, 'day.value').'/';
            }

            if (isset($date['month'])) {
                $formedDateString .= Arr::get($date, 'month.value').'/';
            }

            if (isset($date['year'])) {
                $formedDateString .= Arr::get($date, 'year.value');
            }

            return $formedDateString;
        }

        return $formedDateString;
    }

    public function tags(): array
    {
        return [
            'name' => 'ORCID Scanner',
            'user' => json_encode($this->user->toArray()),
        ];
    }
}
