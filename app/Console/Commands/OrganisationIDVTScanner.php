<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;

use Illuminate\Console\Command;

class OrganisationIDVTScanner extends Command
{
    private const COMPANY_STATUS = [
        'string' => 'company status',
        'inc' => 1,
    ];
    private const COMPANY_TYPE = [
        'string' => 'company type',
        'inc' => 1,
    ];
    private const COMPANY_INCORPORATED = [
        'string' => 'incorporated on',
        'inc' => 1,
    ];
    private const COMPANY_ADDRESS = [
        'string' => 'registered office address',
        'inc' => 1,
    ];
    private const COMPANY_NUMBER = [
        'string' => 'company number',
        'inc' => 1,
    ];
    private const COMPANY_NATURE = [
        'string' => 'nature of business (sic)',
        'inc' => 1,
    ];
    private const COMPANY_PREVIOUS_NAME = [
        'string' => 'previous company names',
        'inc' => 4,
    ];
    private const COMPANY_PREVIOUS_PERIOD = [
        'string' => 'previous company names',
        'inc' => 5,
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
        $response = Http::post(
            env('IDVT_ORG_SCANNER'),
            [
                'url' => env('IDVT_COMPANIES_HOUSE_URL') . '06732228',
            ],
        );

        if ($response->status() !== 200) {
            return; // Should log this
        }

        $govResponseArray = $response->json();

        
    }
}
