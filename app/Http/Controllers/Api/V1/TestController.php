<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $today = now()->format('Y-m-d');

        //    $users = User::query()
        //         ->with([
        //             'registry.trainings' => function ($query) {
        //                 $query->whereRaw('DATE(expires_at) = DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
        //             },
        //         ])
        //         ->whereHas('registry.trainings', function ($query) {
        //             $query->whereRaw('DATE(expires_at) = DATE_ADD(CURDATE(), INTERVAL 30 DAY)');
        //         })
        //         ->get();

        // $users = User::query()
        //     ->with([
        //         'registry.trainings' => function ($query) {
        //             $query->whereRaw('DATE(expires_at) = CURDATE()');
        //         },
        //     ])
        //     ->whereHas('registry.trainings', function ($query) {
        //         $query->whereRaw('DATE(expires_at) = CURDATE()');
        //     })
        //     ->get();

        $days = 30;

        $users = User::query()
            ->where([
                'user_group' => User::GROUP_USERS,
                'id' => 10, // temp
            ])
            ->with([
                // 'registry.trainings',
                'registry.trainings' => function ($query) use ($days) {
                    // $query->whereRaw('DATE(expires_at) = CURDATE()');
                    $query->whereRaw('DATE(expires_at) = DATE_ADD(CURDATE(), INTERVAL ' . $days . ' DAY)');
                    // $query->where('expires_at', today()->toDateString());
                },
            ])
            ->whereHas('registry.trainings', function ($query) use ($days) {
                // $query->whereRaw('DATE(expires_at) = CURDATE()');
                $query->whereRaw('DATE(expires_at) = DATE_ADD(CURDATE(), INTERVAL ' . $days . ' DAY)');
            })
            ->get();


        // $trainings = \DB::table('trainings')->whereRaw("expires_at = '$today'")->get();

        return response()->json([
            'message' => 'success',
            'today' => $today,
            'user' => $users,
            // 'trainings' => $trainings,
        ], 200);
    }
}
