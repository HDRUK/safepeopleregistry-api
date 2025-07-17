<?php

namespace App\Jobs;

use Throwable;
use OrcID;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Education;
use App\Models\Affiliation;
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
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->user->consent_scrape && $this->user->orc_id !== null && $this->user->user_group === User::GROUP_USERS) {
                $this->user->orcid_scanning = 1;
                $this->user->save();

                $token = json_decode(OrcID::getPublicToken($this->user), true);

                $this->accessToken = $token['access_token'];

                $this->getEducations();
                $this->getQualifications();
                $this->getEmployers();

                $this->user->orcid_scanning = 0;
                $this->user->orcid_scanning_completed_at = Carbon::now();
                $this->user->save();
            }
        } catch (Throwable $e) {
            Log::error('OrcID Scanner failed :: ' . $e->getMessage());
        }

        // Nothing to do - either no consent to scrape data, or
        // OrcID hasn't been set. Either way, fail silently.
    }

    private function getEducations(): void
    {
        $record = OrcID::getOrcIDRecord($this->accessToken, $this->user->orc_id, 'educations');
        foreach ($record['affiliation-group'] as $affiliations) {
            foreach ($affiliations['summaries'] as $summary) {
                $education = $summary['education-summary'];
                $title = $education['role-title'];
                $organisation = $education['organization'];

                $dates = [
                    'startDate' => $this->normaliseDate($education['start-date']),
                    'endDate' => $this->normaliseDate($education['end-date']),
                ];

                $education = Education::firstOrCreate([
                    'title' => $title,
                    'from' => $dates['startDate'],
                    'to' => $dates['endDate'],
                    'institute_name' => $organisation['name'],
                    'institute_address' => json_encode($organisation['address']),
                    'institute_identifier' => $organisation['disambiguated-organization']['disambiguated-organization-identifier'],
                    'source' => $organisation['disambiguated-organization']['disambiguation-source'],
                    'registry_id' => $this->user->registry_id,
                ]);
            }
        }
    }

    private function getQualifications(): void
    {
        $record = OrcID::getOrcIDRecord($this->accessToken, $this->user->orc_id, 'qualifications');
        foreach ($record['affiliation-group'] as $affiliations) {
            foreach ($affiliations['summaries'] as $summary) {

                $qualification = $summary['qualification-summary'];
                $title = $qualification['role-title'];
                $organisation = $qualification['organization'];

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
                    'awarding_body_name' => $organisation['name'],
                    'awarding_body_ror' => $organisation['disambiguated-organization']['disambiguated-organization-identifier'],
                    'title' => $title,
                    'expires_at' => $dates['endDate'],
                    'awarded_locale' => $organisation['address']['country'],
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
        foreach ($record['affiliation-group'] as $affiliations) {
            foreach ($affiliations['summaries'] as $summary) {
                $organisation = $summary['employment-summary'];

                $knownOrg = $this->isAKnownOrganisation($organisation['organization']['name']);

                $dates = [
                    'startDate' => $this->normaliseDate($organisation['start-date']),
                    'endDate' => $this->normaliseDate($organisation['end-date']),
                ];

                $preExistingAffiliation = Affiliation::where([
                    'department' => $organisation['department-name'],
                    'from' => $dates['startDate'],
                    'to' => $dates['endDate'],
                    'role' => $organisation['role-title'],
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
                    'department' => $organisation['department-name'],
                    'role' => $organisation['role-title'],
                    'employer_address' => json_encode($organisation['organization']['address']),
                    'ror' => $organisation['organization']['disambiguated-organization']['disambiguated-organization-identifier'],
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
            if (isset($date['month']) && $date['month'] != null) {
                $formedDateString .= $date['month']['value'].'/';
            }

            if (isset($date['year']) && $date['year'] != null) {
                $formedDateString .= $date['year']['value'];
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
