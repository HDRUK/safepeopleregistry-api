<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProjectUserCustodianApproval;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\ProjectHasCustodian;
use App\Models\ProjectHasUser;
use App\Models\Registry;
use Illuminate\Http\JsonResponse;

class ProjectUserCustodianApprovalController extends Controller
{
    use Responses;

    public function show(Request $request, int $custodianId, int $projectId, int $registryId)
    {
        $registry = $this->resolveAndAuthorize($custodianId, $projectId, $registryId);
        if ($registry instanceof JsonResponse) {
            return $registry;
        }

        $puhca = ProjectUserCustodianApproval::where([
            'project_id' => $projectId,
            'user_id' => $registry->user->id,
            'custodian_id' => $custodianId
        ])->exists();

        return $this->OKResponse($puhca);
    }

    public function store(Request $request, int $custodianId, int $projectId, int $registryId)
    {
        $registry = $this->resolveAndAuthorize($custodianId, $projectId, $registryId);
        if ($registry instanceof JsonResponse) {
            return $registry;
        }

        $approval = ProjectUserCustodianApproval::create([
            'project_id' => $projectId,
            'user_id' => $registry->user->id,
            'custodian_id' => $custodianId,
        ]);
        return $this->CreatedResponse($approval);
    }

    public function destroy(Request $request, int $custodianId, int $projectId, int $registryId)
    {
        $registry = $this->resolveAndAuthorize($custodianId, $projectId, $registryId);
        if ($registry instanceof JsonResponse) {
            return $registry;
        }

        $approval = ProjectUserCustodianApproval::where([
            'project_id' => $projectId,
            'user_id' => $registry->user->id,
            'custodian_id' => $custodianId,
        ])->delete();

        return $this->OKResponse(null);
    }

    private function resolveAndAuthorize(int $custodianId, int $projectId, int $registryId): Registry|JsonResponse
    {
        $phc = ProjectHasCustodian::where([
            'project_id' => $projectId,
            'custodian_id' => $custodianId,
        ])->exists();

        if (!$phc) {
            return $this->ForbiddenResponse();
        }

        $registry = Registry::find($registryId);
        if (!$registry || !$registry->user) {
            return $this->NotFoundResponse();
        }

        $phu = ProjectHasUser::where([
            'project_id' => $projectId,
            'user_digital_ident' => $registry->digi_ident,
        ])->exists();

        if (!$phu) {
            return $this->ForbiddenResponse();
        }

        return $registry;
    }
}
