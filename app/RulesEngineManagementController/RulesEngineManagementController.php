<?php

namespace App\RulesEngineManagementController;

use Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\Rules;
use App\Models\EntityModel;
use App\Models\CustodianUser;
use App\Models\CustodianModelConfig;

class RulesEngineManagementController
{
    public static function getCustodianKeyFromHeaders(): string
    {
        return json_decode(Auth::token(), true)['sub'];
    }

    public static function determineUserCustodian(): ?int
    {
        $key = RulesEngineManagementController::getCustodianKeyFromHeaders();
        $user = User::where('keycloak_id', $key)->first();

        if (!$user) {
            return null;
        }

        $custodianId = CustodianUser::where('id', $user->custodian_user_id)
            ->select('custodian_id')
            ->pluck('custodian_id')
            ->first();

        if (!$custodianId) {
            return null;
        }

        return $custodianId;
    }

    public static function loadCustodianRules(Request $request): ?Collection
    {
        $custodianId = RulesEngineManagementController::determineUserCustodian($request);
        if (!$custodianId) {
            return null;
        }

        $modelConfig = CustodianModelConfig::where([
            'custodian_id' => $custodianId,
            'active' => 1,
        ])->select('entity_model_id')
        ->pluck('entity_model_id');

        if (!$modelConfig) {
            return null;
        }

        $activeModels = EntityModel::whereIn('id', $modelConfig)->get();
        if (!$activeModels) {
            return null;
        }

        return $activeModels;
    }

    public static function evaluateRulesEngine(array $payload, Collection $config): array
    {
        $responseArray = [];

        foreach ($config as $c) {
            if (isset($payload['data'])) {
                foreach ($payload['data'] as $user) {
                    $rulesServiceUrl = env('RULES_ENGINE_SERVICE', 'https://rules-engine.test') .
                        env('RULES_ENGINE_PROJECT_ID', '298357293857') . '/evaluate/' .
                        env('RULES_ENGINE_EVAL_MODEL', $c->file_path);

                    $response = Http::withHeaders([
                        'X-Access-Token' => env('RULES_ENGINE_PROJECT_TOKEN'),
                    ])
                    ->acceptJson()
                    ->post($rulesServiceUrl, [
                        'context' => $payload,
                        'trace' => true,
                    ]);

                    $responseArray[$user['id']] = $response->json();
                }
            } else {
                $rulesServiceUrl = env('RULES_ENGINE_SERVICE', 'https://rules-engine.test') .
                env('RULES_ENGINE_PROJECT_ID', '298357293857') . '/evaluate/' .
                env('RULES_ENGINE_EVAL_MODEL', $c->file_path);

                $response = Http::withHeaders([
                    'X-Access-Token' => env('RULES_ENGINE_PROJECT_TOKEN'),
                ])
                ->acceptJson()
                ->post($rulesServiceUrl, [
                    'context' => $payload,
                    'trace' => true,
                ]);

                $responseArray[$payload['id']] = $response->json();
            }
        }

        return $responseArray;
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

        $rules = Rules::all();
        return response()->json([
            'data' => $rules
        ]);

    }
}
