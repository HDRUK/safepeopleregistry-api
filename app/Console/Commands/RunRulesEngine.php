<?php

namespace App\Console\Commands;

use Http;
use Illuminate\Console\Command;

class RunRulesEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rules-eval';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs an evaluation against the rules engine to determine suitability of registry data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Access-Token' => env('RULES_ENGINE_PROJECT_TOKEN'),
        ];

        $body = [
            'context' => [
                'customer' => [
                    'country' => 'RU',
                ],
            ],
            'trace' => 'false',
        ];

        $response = Http::withHeaders($headers)->post(
            env('RULES_ENGINE_SERVICE').env('RULES_ENGINE_PROJECT_ID').'/evaluate/'.env('RULES_ENGINE_DOCUMENT_ID'),
            $body
        );
        return $response;

        // Debug line for the time being, to prove concept.
        dd($response);
    }
}
