<?php

namespace App\Jobs;

use \stdClass;

use Carbon\Carbon;
use App\Models\IDVTPlugin;
use App\Models\Organisation;

use App\Traits\CommonFunctions;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Facade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrganisationIDVT implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use CommonFunctions;

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
        ]
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

    private float $numPositive = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(Organisation $org, string $nameToCheck, string $numberToCheck, string $addressToCheck, string $postcodeToCheck)
    {
        $this->company = new stdClass();
        $this->company->numPositive = 0;

        $this->nameToCheck = $nameToCheck;
        $this->numberToCheck = $numberToCheck;
        $this->addressToCheck = $addressToCheck;
        $this->postcodeToCheck = $postcodeToCheck;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::post(
            env('IDVT_ORG_SCANNER'),
            [
                'url' => env('IDVT_COMPANIES_HOUSE_URL') . $this->numberToCheck,
            ],
        );

        if ($response->status() !== 200) {
            // Company doesn't exist, therefore we abort and mark as unverified
            $this->organisation->update([
                'idvt_result' => 0,
                'idvt_result_perc' => 0,
                'idvt_errors' => 'company does not exist in gov record',
                'idvt_completed_at' => Carbon::now(),
                'verified' => false,
            ]);
            return;
        }

        $this->loadCriteria($response->json()['data']);
        $this->renderVerdict();
    }

    private function loadCriteria(array $govResponseArray): void
    {
        foreach (self::COMPANY_INFO as $metric) {
            foreach ($govResponseArray as $key => $value) {
                if ($metric['string'] === strtolower($value)) {
                    $this->company->{$metric['var']} = trim(htmlspecialchars($govResponseArray[$key + $metric['inc']]));
                }
            }
        }        
    }

    private function renderVerdict(): void
    {
        $plugins = IDVTPlugin::all();
        foreach ($plugins as $p) {
            $args = [];

            foreach (explode(', ', $p->args) as $a) {
                $args[] = $this->{$a};
            }

            // I know...! But explicit safe-guarding and discussions
            // have happened to ensure safe usage. Basically, we're
            // in control of everything being eval'd.
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

        $perc = ($this->company->numPositive / count($plugins) * 100);
        $verdict = (($this->company->numPositive / count($plugins) * 100) >= 
            (float)$this->getSystemConfig('IDVT_ORG_VERIFY_PERCENT'));
        
        $this->organisation->update([
            'idvt_result' => $verdict,
            'idvt_result_perc' => $perc,
            'idvt_errors' => isset($this->company->errors) ? json_encode($this->company->errors) : null,
            'idvt_completed_at' => Carbon::now(),
            'verified' => $verdict,
        ]);
    }
}
