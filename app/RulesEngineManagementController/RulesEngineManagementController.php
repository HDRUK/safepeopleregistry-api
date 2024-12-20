<?php

namespace App\RulesEngineManagementController;

use Illuminate\Support\Facades\Http;

class RulesEngineManagementController
{
    public static function callRulesEngine(array $payload): array
    {
        $rulesServiceUrl = env('RULES_ENGINE_SERVICE') .
            env('RULES_ENGINE_PROJECT_ID') . '/evaluate/' .
            env('RULES_ENGINE_EVAL_MODEL');

        $response = Http::withHeaders([
            'X-Access-Token' => env('RULES_ENGINE_PROJECT_TOKEN'),
        ])
        ->acceptJson()
        ->post($rulesServiceUrl, [
            'context' => $payload,
            'trace' => true,
        ]);

        return $response->json();
    }
}
