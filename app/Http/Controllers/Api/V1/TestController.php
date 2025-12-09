<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ProjectHasCustodian;
use App\Http\Controllers\Controller;
use App\Models\ProjectHasOrganisation;
use App\Models\CustodianHasProjectUser;
use App\Models\CustodianHasProjectOrganisation;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $projectCustodianIds = ProjectHasCustodian::where('project_id', 1)
            ->get()
            ->pluck('custodian_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        $users = User::where('user_group', 'CUSTODIANS')
            ->whereIn('custodian_user_id', function ($query) use ($projectCustodianIds) {
                $query->select('id')
                    ->from('custodian_users')
                    ->whereIn('custodian_id', $projectCustodianIds);
            })
            ->get();

        // $users = CustodianHasProjectOrganisation::query()
        //     ->with([
        //         'projectOrganisation.organisation',
        //     ])
        //     ->whereHas('projectOrganisation.project', function ($query) {
        //         $query->where('id', 1);
        //     })
        //     ->get()
        //     ->pluck('projectOrganisation.organisation.id')
        //     ->filter()
        //     ->unique()
        //     ->values()
        //     ->toArray();


// {
//     "message": "success",
//     "data": [
//         10,
//         11,
//         14,
//         17
//     ]
// }
        return response()->json([
            'message' => 'success',
            'data' => $users
        ], 200);
    }
}
