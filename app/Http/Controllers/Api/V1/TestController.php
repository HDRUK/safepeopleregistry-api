<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CustodianHasProjectOrganisation;
use App\Models\ProjectHasOrganisation;
use App\Models\CustodianHasProjectUser;

class TestController extends Controller
{
    public function test(Request $request)
    {
        // $test = ProjectHasOrganisation::query()
        //     ->with([
        //         'project',
        //         'organisation'
        //     ])
        //     ->get();
        $test = CustodianHasProjectOrganisation::query()
            ->where([
                'project_has_organisation_id' => 1,
                'custodian_id' => 1,
            ])
            ->with([
                'projectOrganisation.organisation',
                'projectOrganisation.project'
            ])
            ->first();

        $project = $test->projectOrganisation->project;
        $organisation = $test->projectOrganisation->organisation;

        $userIds = CustodianHasProjectUser::query()
            ->where('custodian_id', 1)
            ->with([
                'projectHasUser.registry.user:id,registry_id,first_name,last_name,email',
                'projectHasUser.project',
            ])
            ->whereHas('projectHasUser.project', function ($query) {
                $query->where('id', 1);
            })
            ->get()->pluck('projectHasUser.registry.user.id')->toArray();

        return response()->json([
            'message' => 'success',
            // 'data' => $test,
            // 'data' => $project
            // 'data' => $organisation
            'data' => $userIds
        ], 200);
    }
}
