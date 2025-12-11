<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use App\Http\Controllers\Controller;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $projects = Project::query()
            ->where('id', 5)
            ->with([
                'projectHasSponsorships.sponsor',
                'projectHasSponsorships.custodianHasProjectHasSponsorship.modelState.state',
            ])
            ->get();

        //    if (Feature::active('sponsorship')) {
        //         return response()->json([
        //             'message' => 'active',
        //         ], 200);
        //     }

        //     if (Feature::inactive('sponsorship')) {
        //         return response()->json([
        //             'message' => 'inactive',
        //         ], 200);
        //     }

        return response()->json([
            'message' => 'success',
            'data' => $projects,
        ], 200);
    }
}
