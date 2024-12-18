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
    /**
     * @OA\Get(
     *     path="/v1/approvals/{entity_type}/{id}/custodian/{custodian_id}",
     *     summary="Get approvals by entity type and custodian ID",
     *     tags={"Approvals"},
     *     @OA\Parameter(
     *         name="entity_type",
     *         in="path",
     *         required=true,
     *         description="The entity type (USER or ORGANISATION)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the user or organisation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="custodian_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the custodian",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No approvals found"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Unknown entity type"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving approvals"
     *     )
     * )
     */
    public function getEntityHasCustodianApproval(Request $request, string $entityType, string $id, string $custodianId): JsonResponse
    {
        try {
            switch (strtoupper($entityType)) {
                case 'USER':
                    $approvals = UserHasCustodianApproval::where([
                        ['custodian_id', '=', $custodianId],
                        ['user_id', '=', $id]
                    ])->get();
                    break;

                case 'ORGANISATION':
                    $approvals = OrganisationHasCustodianApproval::where([
                        ['custodian_id', '=', $custodianId],
                        ['organisation_id', '=', $id]
                    ])->get();
                    break;

                default:
                    return response()->json([
                        'message' => 'Unknown entity type',
                        'data' => [],
                    ], 400);
            }

            if ($approvals->isEmpty()) {
                return response()->json([
                    'message' => 'No approvals found',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'success',
                'data' => $approvals,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error retrieving approvals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
