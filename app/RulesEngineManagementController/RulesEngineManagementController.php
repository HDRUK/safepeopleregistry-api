<?php

namespace App\RulesEngineManagementController;

use Illuminate\Support\Facades\Http;

class RulesEngineManagementController
{
    public static function callRulesEngine(array $payload): array
    {
        $rulesServiceUrl = env('RULES_ENGINE_SERVICE', 'https://rules-engine.test') .
            env('RULES_ENGINE_PROJECT_ID', '298357293857') . '/evaluate/' .
            env('RULES_ENGINE_EVAL_MODEL', 'something.json');

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
