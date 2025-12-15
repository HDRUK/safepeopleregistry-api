<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProjectHasCustodian;
use App\Models\CustodianUser;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $custodianIds = ProjectHasCustodian::where('project_id', 1)->get()->pluck('custodian_id')->toArray();

        $userCustodians = User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $custodianIds)->pluck('id')
        )->get();

        return response()->json([
            'message' => 'success',
            'custodianIds' => $custodianIds,
            'userCustodians' => $userCustodians,
        ], 200);
    }
}
