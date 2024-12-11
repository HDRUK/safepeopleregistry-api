<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\OrganisationHasCustodianApproval;
use App\Models\User;
use App\Models\UserHasCustodianApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function store(Request $request, string $entityType): JsonResponse
    {
        try {
            $input = $request->all();

            switch (strtoupper($entityType)) {
                case 'ORGANISATION':
                    $organisation = Organisation::where('id', $input['organisation_id'])->first();
                    $custodian = Custodian::where('id', $input['custodian_id'])->first();

                    $ohia = OrganisationHasCustodianApproval::create([
                        'organisation_id' => $organisation->id,
                        'custodian_id' => $custodian->id,
                    ]);

                    return response()->json([
                        'message' => 'success',
                        'data' => $ohia !== null,
                    ], 200);

                case 'RESEARCHER':
                    $user = User::where('id', $input['user_id'])->first();
                    $custodian = Custodian::where('id', $input['custodian_id'])->first();

                    $uhia = UserHasCustodianApproval::create([
                        'user_id' => $user->id,
                        'custodian_id' => $custodian->id,
                    ]);

                    return response()->json([
                        'message' => 'success',
                        'data' => $uhia !== null,
                    ], 200);

                default:
                    return response()->json([
                        'message' => 'unknown operation',
                        'data' => null,
                    ], 400);

            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete(Request $request, string $entityType, string $id, string $custodianId)
    {
        try {
            $input = $request->all();

            switch (strtoupper($entityType)) {
                case 'ORGANISATION':
                    $organisation = Organisation::where('id', $id)->first();
                    $custodian = Custodian::where('id', $custodianId)->first();

                    OrganisationHasCustodianApproval::where([
                        'organisation_id' => $organisation->id,
                        'custodian_id' => $custodian->id,
                    ])->delete();

                    return response()->json([
                        'message' => 'success',
                        'data' => null,
                    ]);
                case 'RESEARCHER':
                    $user = User::where('id', $id)->first();
                    $custodian = Custodian::where('id', $custodianId)->first();

                    UserHasCustodianApproval::where([
                        'user_id' => $user->id,
                        'custodian_id' => $custodian->id,
                    ])->delete();

                    return response()->json([
                        'message' => 'success',
                        'data' => null,
                    ]);
                default:
                    return response()->json([
                        'message' => 'unknown operation',
                        'data' => null,
                    ]);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }
}
