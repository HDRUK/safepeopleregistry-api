<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProjectUserCustodianApproval;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\ProjectHasCustodian;
use App\Models\ProjectHasUser;
use App\Models\Registry;

class ProjectUserCustodianApprovalController extends Controller
{
    use Responses;

    public function show(Request $request, int $custodianId, int $projectId, int $registryId)
    {
        $phc = ProjectHasCustodian::where([
            'project_id' => $projectId,
            'custodian_id' => $custodianId,
        ])->exists();

        if (!$phc) {
            return $this->NotFoundResponse();
        }

        $registry = Registry::where([
            'id' => $registryId
        ])->select('digi_ident')->first();

        $phu = ProjectHasUser::where(
            [
                'project_id' => $projectId,
                //'affiliation_id' => $affiliationId,
                'user_digital_ident' => $registry->digi_ident,
            ]
        )->get();


        if (!$phu) {
            return $this->NotFoundResponse();
        }

        return $this->OKResponse($phu);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_user_id' => 'required|exists:project_has_users,id',
            'custodian_id' => 'required|exists:custodians,id',
        ]);

        $approval = ProjectUserCustodianApproval::create($validated);

        return response()->json($approval, 201);
    }

    public function destroy(ProjectUserCustodianApproval $approval)
    {
        $approval->delete();

        return $this->OKResponse(null);
    }
}
