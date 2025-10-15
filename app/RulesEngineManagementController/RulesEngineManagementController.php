<?php

namespace App\RulesEngineManagementController;

use Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CustodianUser;
use App\Models\DecisionModel;
use App\Models\EntityModelType;
use Illuminate\Support\Facades\Log;
use App\Models\CustodianModelConfig;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method static \Illuminate\Database\Eloquent\Collection|null loadCustodianRules(\Illuminate\Http\Request $request, array $validationType)
 */
class RulesEngineManagementController
{
    public static function getCustodianKeyFromHeaders(): string
    {
        $obj = json_decode(Auth::token(), true);

        if (isset($obj['sub'])) {
            return $obj['sub'];
        }

        return '';
    }

    public static function determineUserCustodian(): mixed
    {
        $key = self::getCustodianKeyFromHeaders();
        $user = User::where('keycloak_id', $key)->first();

        if (!$user || $user->user_group !== 'CUSTODIANS') {
            return null;
        }

        $custodianId = CustodianUser::where('id', $user->custodian_user_id)
            ->select('custodian_id')
            ->pluck('custodian_id');

        if (!$custodianId) {
            return null;
        }

        return $custodianId;
    }

    public static function loadCustodianRules(Request $request, array $validationType): ?Collection
    {
        $custodianId = self::determineUserCustodian();
        if (!$custodianId) {
            return null;
        }

        $entityModelTypeIds = [];

        if (filled($validationType)) {
            $entityModelTypeIds = EntityModelType::whereIn('name', $validationType)->pluck('id')->toArray();
        } else {
            $entityModelTypeIds = EntityModelType::whereIn('name', [
                EntityModelType::USER_VALIDATION_RULES,
                EntityModelType::ORG_VALIDATION_RULES
            ])->pluck('id')->toArray();
        }

        Log::info('loadCustodianRules', [
            'entityModelTypeIds' => $entityModelTypeIds
        ]);
        
        $modelConfig = CustodianModelConfig::where([
            'custodian_id' => $custodianId,
            'active' => 1,
        ])->select('entity_model_id')
        ->pluck('entity_model_id')->toArray();
        Log::info('loadCustodianRules', [
            'modelConfig' => $modelConfig
        ]);

        if (!$modelConfig) {
            return null;
        }

        $activeModels = DecisionModel::whereIn('id', $modelConfig)->whereIn('entity_model_type_id', $entityModelTypeIds)->get();
        Log::info('loadCustodianRules', [
            'activeModels' => $activeModels
        ]);
        if (!$activeModels) {
            return null;
        }

        return $activeModels;
    }
}
