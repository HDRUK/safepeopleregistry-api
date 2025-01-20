<?php

namespace App\RulesEngineManagementController;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @method static RulesEngineManagementController evaluateRulesEngine(array $payload)
 */
class RulesEngineManagementController
{
    public static function evaluateRulesEngine(array $payload): array
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

    public function getRules(Request $request): JsonResponse
    {

        // Can't find the API route to return the JSON
        // - https://docs.gorules.io/reference/get_api-projects-projectid-releases
        // - Inspecting download buttons from the gorules FE I see:
        //     - http://localhost:4200/api/projects/dc991d18-1768-44dc-bdb7-b8ce4df8c84a/test-events?documentId=b2b444df-f571-41fb-aa40-1ef4b4f2e26a
        //       being called
        //     - Get 401 unauthorised? Don't know how to find the document ID?
        //     - gorules.io needs documentation about how you can use the API to get the JSON and/or simple names of the rules...

        /*
        $rulesServiceUrl = env('RULES_ENGINE_SERVICE', 'https://rules-engine.test') .
            env('RULES_ENGINE_PROJECT_ID', '298357293857') . '/releases' ;
        #env('RULES_ENGINE_EVAL_MODEL', 'something.json');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env("RULES_ENGINE_PERSONAL_ACCESS_TOKEN"),
            'X-Access-Token' => env('RULES_ENGINE_PROJECT_TOKEN'),
        ])
        ->acceptJson()
        ->get($rulesServiceUrl);

        return response()->json($response->json());
        */
        // Mock return for now
        $filePath = storage_path('mocks/decisionTree.json');
        $jsonData = json_decode(file_get_contents($filePath), true);
        return response()->json(['data' => $jsonData]);
    }
}
