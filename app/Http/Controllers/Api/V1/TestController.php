<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CustodianUser;
use App\Http\Controllers\Controller;
use App\Models\CustodianHasProjectUser;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $custodianIds = CustodianHasProjectUser::query()
            ->with([
                'projectHasUser.affiliation.organisation' => function ($query) {
                    $query->where('id', 1);
                }
            ])
            ->whereHas('projectHasUser.affiliation.organisation', function ($query) {
                $query->where('id', 1);
            })
            ->get()->pluck('custodian_id')->filter()->unique()->values()->toArray();

        $users = User::whereIn(
            'custodian_user_id',
            CustodianUser::where('custodian_id', $custodianIds)
                ->pluck('id')
        )->get();

        return response()->json([
            'message' => 'success',
            'custodianIds' => $custodianIds,
            'users' => $users
        ], 200);
    }
}
