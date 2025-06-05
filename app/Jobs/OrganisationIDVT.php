<?php

namespace App\Jobs;

use App\Models\IDVTPlugin;
use App\Models\Organisation;
use App\Traits\CommonFunctions;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use stdClass;

class OrganisationIDVT implements ShouldQueue
{
    use CommonFunctions;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private stdClass $company;

    private const COMPANY_INFO = [
        'COMPANY_NAME' => [
            'var' => 'companyName',
            'string' => 'find and update company information',
            'inc' => 2,
        ],
        'COMPANY_STATUS' => [
            'var' => 'companyStatus',
            'string' => 'company status',
            'inc' => 1,
        ],
        'COMPANY_TYPE' => [
            'var' => 'companyType',
            'string' => 'company type',
            'inc' => 1,
        ],
        'COMPANY_INCORPORATED' => [
            'var' => 'incorporatedOn',
            'string' => 'incorporated on',
            'inc' => 1,
        ],
        'COMPANY_ADDRESS' => [
            'var' => 'companyAddress',
            'string' => 'registered office address',
            'inc' => 1,
        ],
        'COMPANY_NUMBER' => [
            'var' => 'companyNumber',
            'string' => 'company number',
            'inc' => 1,
        ],
        'COMPANY_NATURE' => [
            'var' => 'natureOfBusiness',
            'string' => 'nature of business (sic)',
            'inc' => 1,
        ],
        'COMPANY_PREVIOUS_NAME' => [
            'var' => 'previousCompanyName',
            'string' => 'previous company names',
            'inc' => 3,
        ],
        'COMPANY_PREVIOUS_PERIOD_FROM' => [
            'var' => 'previousCompanyPeriodFrom',
            'string' => 'period',
            'inc' => 2,
        ],
        'COMPANY_PREVIOUS_PERIOD_TO' => [
            'var' => 'previousCompanyPeriodTo',
            'string' => 'previous company names',
            'inc' => 5,
        ],
    ];

    private const PEOPLE_INFO = [
        'PEOPLE_OFFICERS' => [
            'var' => 'personWithSignificantControl',
            'string' => 'current officers',
            'inc' => 2,
        ],
        'PEOPLE_OFFICERS_ACTIVE' => [
            'var' => 'personWithSignificantControlActive',
            'string' => 'current officers',
            'inc' => 6,
        ],
        'PEOPLE_OFFICERS_ROLE' => [
            'var' => 'personWithSignificantControlRole',
            'string' => 'correspondence address',
            'inc' => 4,
        ],
        'PEOPLE_OFFICERS_APPOINTED' => [
            'var' => 'personWithSignificantControlAppointedOn',
            'string' => 'current officers',
            'inc' => 11,
        ],
    ];

    private ?Organisation $organisation;

    private string $nameToCheck = '';

    private string $numberToCheck = '';

    private string $addressToCheck = '';

    private string $postcodeToCheck = '';

    private array $criteria = [
        'companyName',
        'companyNumber',
        'companyAddress',
        'postcode',
        'natureOfBusiness',
    ];

    private array $sicExclusions = [
        '74990', // Non-trading
        '99999', // Dormant
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(Organisation $org)
    {
        $this->company = new stdClass();
        $this->company->numPositive = 0;

        $this->organisation = $org;

        $this->nameToCheck = $org->organisation_name;
        $this->numberToCheck = $org->companies_house_no;
        $this->addressToCheck = $org->address_1;
        $this->postcodeToCheck = $org->postcode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $responseCompany = Http::post(
            config('speedi.system.idvt_org_scanner'),
            [
                'url' => config('speedi.system.idvt_companies_house_url') . $this->numberToCheck,
            ],
        );

        $responsePeople = Http::post(
            config('speedi.system.idvt_org_scanner'),
            [
                'url' => config('speedi.system.idvt_companies_house_url') . $this->numberToCheck.'/officers',
            ],
        );

        if ($responseCompany->status() !== 200) {
            // Company doesn't exist, therefore we abort and mark as unverified
            $this->organisation->update([
                'idvt_result' => 0,
                'idvt_result_perc' => 0,
                'idvt_errors' => 'company does not exist in gov record',
                'idvt_completed_at' => Carbon::now()->toDateTimeString(),
                'verified' => false,
            ]);
            $responseCompany->close();
            $responsePeople->close();
            return;
        }

        $this->loadCriteria(
            $responseCompany->json()['data'],
            $responsePeople->json()['data']
        );
        $responseCompany->close();
        $responsePeople->close();

        $this->renderVerdict();
    }

    private function loadCriteria(
        array $govCompanyResponseArray,
        array $govPeopleResponseArray
    ): void {
        foreach (self::COMPANY_INFO as $metric) {
            foreach ($govCompanyResponseArray as $key => $value) {
                if ($metric['string'] === strtolower($value)) {
                    $this->company->{$metric['var']} = trim(htmlspecialchars($govCompanyResponseArray[$key + $metric['inc']]));
                }
            }
        }

        foreach (self::PEOPLE_INFO as $metric) {
            foreach ($govPeopleResponseArray as $key => $value) {
                if ($metric['string'] === strtolower($value)) {
                    $this->company->{$metric['var']} = trim(htmlspecialchars($govPeopleResponseArray[$key + $metric['inc']]));
                }
            }
        }
    }

    private function renderVerdict(): void
    {
        $percentageSuccess = 0;
        $verdict = false;

        $plugins = IDVTPlugin::all();
        foreach ($plugins as $p) {
            $args = [];
            foreach (explode(', ', $p->args) as $a) {
                $args[] = $this->{$a};
            }

            // I know...! But explicit safe-guarding and discussions
            // have happened to ensure safe usage. Basically, we're
            // in control of everything being eval'd, therefore no
            // outside influence can abuse it's use here.
            eval($p->config);

            if (function_exists($p->function)) {
                $retVal = call_user_func_array($p->function, $args);
                if ($retVal['result'] === false) {
                    $this->company->numPositive -= ($this->company->numPositive * $this->getSystemConfig('IDVT_ORG_SIC_WEIGHT_DECREASE'));
                    $this->company->errors[] = $retVal['errors'];
                } else {
                    $this->company->numPositive++;
                }
            }
        }

        if ($this->company->numPositive === 0) {
            $percentageSuccess = 0;
            $verdict = false;

            $this->finaliseIDVTRecord($percentageSuccess, $verdict);
            return;
        }

        $percentageSuccess = ($this->company->numPositive / count($plugins) * 100);
        $verdict = (($this->company->numPositive / count($plugins) * 100) >=
            (float) $this->getSystemConfig('IDVT_ORG_VERIFY_PERCENT'));

        $this->finaliseIDVTRecord($percentageSuccess, $verdict);
    }

    private function finaliseIDVTRecord(float $percentage, bool $verdict)
    {
        $this->organisation->update([
            'idvt_result' => $verdict,
            'idvt_result_perc' => $percentage,
            'idvt_errors' => isset($this->company->errors) ? json_encode($this->company->errors) : null,
            'idvt_completed_at' => Carbon::now()->toDateTimeString(),
            'verified' => $verdict,
        ]);
    }
}
