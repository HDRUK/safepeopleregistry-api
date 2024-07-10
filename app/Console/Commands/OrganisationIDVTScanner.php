<?php

namespace App\Console\Commands;

use \stdClass;
use App\Models\IDVTPlugin;
use App\Traits\CommonFunctions;

use Illuminate\Support\Facades\Http;

use Illuminate\Console\Command;

class OrganisationIDVTScanner extends Command
{
    use CommonFunctions;

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

    public stdClass $company;

    private string $nameToCheck = 'Woolworths';
    private string $numberToCheck = '06732228';
    private string $addressToCheck = 'First Floor Skyways House Speke Road';
    private string $postcodeToCheck = 'L70 1AB';

    private array $criteria = [
        'companyName',
        'companyNumber',
        'companyAddress',
        'companyPostcode',
        'natureOfBusiness',
    ];

    private array $sicExclusions = [
        '74990', // Non-trading
        '99999', // Dormant
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:organisation-i-d-v-t-scanner';

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
        $this->company = new stdClass();
        $this->company->numPositive = 0;

        $response = Http::post(
            env('IDVT_ORG_SCANNER'),
            [
                'url' => env('IDVT_COMPANIES_HOUSE_URL') . $this->numberToCheck,
            ],
        );

        if ($response->status() !== 200) {
            return; // Should log this
        }

        $govResponseArray = $response->json()['data'];

        foreach (self::COMPANY_INFO as $metric) {
            foreach ($govResponseArray as $key => $value) {
                if ($metric['string'] === strtolower($value)) {
                    $this->company->{$metric['var']} = trim(htmlspecialchars($govResponseArray[$key + $metric['inc']]));
                }
            }
        }

        return $this->renderVerdict();
    }

    private function renderVerdict(): array
    {
        $plugins = IDVTPlugin::all();

        foreach ($plugins as $p) {
            $args = [];

            foreach (explode(', ', $p->args) as $a) {
                $args[] = $this->{$a};
            }

            // I know...! But specific safe-guarding has been put in
            // place to ensure safe inclusion.
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
            (int)$this->getSystemConfig('IDVT_ORG_VERIFY_PERCENT'));

        dd([
            'result' => $verdict,
            'percent' => $perc,
            'errors' => (isset($this->company->errors) ? $this->company->errors : null),
        ]);

        return [
            'result' => $verdict,
            'percent' => $perc,
            'errors' => (isset($this->company->errors) ? $this->company->errors : null),
        ];
    }
}
