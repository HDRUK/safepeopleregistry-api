<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use App\Models\ProjectHasCustodian;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasOrganisation;
use App\Models\CustodianHasProjectUser;
use App\Models\CustodianHasProjectOrganisation;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $projects = Project::query()
            ->where('id', 5)
            ->with([
                'projectHasSponsorhips.sponsor'
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
