<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EntityModelType;
use App\Http\Controllers\Controller;
use App\Models\Custodian;
use App\Models\CustodianHasProjectUser;
use App\Services\DecisionEvaluatorService;

class TestController extends Controller
{
    protected $decisionEvaluator = null;
    
    public function test(Request $request)
    {
        // $custodianIds = Custodian::query()
        //     ->pluck('id')
        //     ->toArray();

        // foreach ($custodianIds as $custodianId) {
        //     //
        // }


        $users = CustodianHasProjectUser::query()
            ->where([
                'custodian_id' => 1,
            ])
            ->with([
                'projectHasUser.registry.user'
            ])
            ->get();
            // ->get()
            // ->map(fn($item) => $item->projectHasUser?->registry?->user?->id)
            // ->filter() // Remove nulls
            // ->unique()
            // ->values()
            // ->toArray();

        // $this->decisionEvaluator = new DecisionEvaluatorService([EntityModelType::USER_VALIDATION_RULES]);

        // $userId = 10;

        // $user = User::with([
        //         'permissions',
        //         'registry',
        //         'registry.files',
        //         'registry.affiliations',
        //         'pendingInvites',
        //         'organisation',
        //         'departments',
        //         'registry.identity',
        //         'registry.education',
        //         'registry.trainings',
        //     ])->where('id', $userId)->first()->toArray();
        // $user['rules'] = $this->decisionEvaluator->evaluate($user);

        return response()->json([
            'message' => 'success',
            // 'custodians' => $custodianIds,
            'data' => $users,
        ], 200);
    }
}
