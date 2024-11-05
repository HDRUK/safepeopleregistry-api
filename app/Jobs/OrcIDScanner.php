<?php

namespace App\Jobs;

use OrcID;
use Carbon\Carbon;
use App\Models\Accreditation;
use App\Models\Education;
use App\Models\Employment;
use App\Models\RegistryHasAccreditation;
use App\Models\RegistryHasEducation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrcIDScanner implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $user = null;

    private $accessToken = null;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->user->consent_scrape && $this->user->orc_id !== null) {
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

                $education = Education::create([
                    'title' => $title,
                    'from' => $dates['startDate'],
                    'to' => $dates['endDate'],
                    'institute_name' => $organisation['name'],
                    'institute_address' => json_encode($organisation['address']),
                    'institute_identifier' => $organisation['disambiguated-organization']['disambiguated-organization-identifier'],
                    'source' => $organisation['disambiguated-organization']['disambiguation-source'],
                ]);

                RegistryHasEducation::create([
                    'registry_id' => $this->user->registry_id,
                    'education_id' => $education->id,
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

                $accreditation = Accreditation::create([
                    'awarded_at' => $dates['startDate'],
                    'awarding_body_name' => $organisation['name'],
                    'awarding_body_ror' => $organisation['disambiguated-organization']['disambiguated-organization-identifier'],
                    'title' => $title,
                    'expires_at' => $dates['endDate'],
                    'awarded_locale' => $organisation['address']['country'],
                ]);

                RegistryHasAccreditation::create([
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

                $dates = [
                    'startDate' => $this->normaliseDate($organisation['start-date']),
                    'endDate' => $this->normaliseDate($organisation['end-date']),
                ];

                $employment = Employment::create([
                    'employer_name' => $organisation['organization']['name'],
                    'from' => $dates['startDate'],
                    'to' => $dates['endDate'],
                    'is_current' => ($dates['endDate'] === '') ? 1 : 0,
                    'department' => $organisation['department-name'],
                    'role' => $organisation['role-title'],
                    'employer_address' => json_encode($organisation['organization']['address']),
                    'ror' => $organisation['organization']['disambiguated-organization']['disambiguated-organization-identifier'],
                    'registry_id' => $this->user->registry_id,
                ]);
            }
        }
    }

    private function normaliseDate(?array $date): string
    {
        $formedDateString = '';

        if (is_array($date)) {
            if (isset($date['month']) && $date['month'] !== null) {
                $formedDateString .= $date['month']['value'].'/';
            }

            if (isset($date['year']) && $date['year'] !== null) {
                $formedDateString .= $date['year']['value'];
            }

            return $formedDateString;
        }

        return $formedDateString;
    }
}
