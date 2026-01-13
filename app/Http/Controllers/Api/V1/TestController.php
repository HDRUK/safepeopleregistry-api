<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $custodianId = [1];
        $users = User::query()
            ->where([
                'user_group' => User::GROUP_CUSTODIANS,
                'unclaimed' => 0,
            ])
            ->with([
                'custodian_user' => function ($query) use ($custodianId) {
                    $query->whereIn('custodian_id', $custodianId);
                },
            ])
            ->whereHas('custodian_user', function ($query) use ($custodianId) {
                $query->whereIn('custodian_id', $custodianId);
            })
            ->get();

        return response()->json([
            'message' => 'success',
            'users' => $users
        ], 200);
    }
}
