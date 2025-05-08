<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\Organisation;
use App\Models\Custodian;
use App\Models\OrganisationHasCustodianApproval;

class OrganisationCustodianApprovalController extends Controller
{
    use Responses;

    public function show(Request $request, int $custodianId, int $organisationId)
    {
        $organisation = Organisation::where('id', $organisationId)->first();
        $custodian = Custodian::where('id', $custodianId)->first();

        $ohia = OrganisationHasCustodianApproval::create([
            'organisation_id' => $organisation->id,
            'custodian_id' => $custodian->id,
        ])->latest('created_at')->first();

        return $this->OKResponse($ohia);
    }

    public function store(Request $request, int $custodianId, int $organisationId)
    {
        try {
            $validated = $request->validate([
                'approved' => 'required|integer|in:0,1',
                'comment' => 'required|string',
            ]);

            $organisation = Organisation::find($organisationId);
            $custodian = Custodian::find($custodianId);

            if (!$organisation || !$custodian) {
                return $this->NotFoundResponse();
            }

            $approval = OrganisationHasCustodianApproval::create([
                'organisation_id' => $organisation->id,
                'custodian_id' => $custodian->id,
                'approved' => $validated['approved'],
                'comment' => $validated['comment'],
            ]);

            return $this->CreatedResponse($approval);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }
}
