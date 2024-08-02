<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Issuer;
use App\Models\Organisation;
use App\Models\OrganisationHasIssuerApproval;
use App\Models\User;
use App\Models\UserHasIssuerApproval;
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
                    $issuer = Issuer::where('id', $input['issuer_id'])->first();

                    $ohia = OrganisationHasIssuerApproval::create([
                        'organisation_id' => $organisation->id,
                        'issuer_id' => $issuer->id,
                    ]);

                    return response()->json([
                        'message' => 'success',
                        'data' => $ohia !== null,
                    ], 200);
                    break;
                case 'RESEARCHER':
                    $user = User::where('id', $input['user_id'])->first();
                    $issuer = Issuer::where('id', $input['issuer_id'])->first();

                    $uhia = UserHasIssuerApproval::create([
                        'user_id' => $user->id,
                        'issuer_id' => $issuer->id,
                    ]);

                    return response()->json([
                        'message' => 'success',
                        'data' => $uhia !== null,
                    ], 200);
                    break;
                default:
                    return response()->json([
                        'message' => 'unknown operation',
                        'data' => null,
                    ], 400);
                    break;
            }

            return response()->json([
                'message' => 'unknown request',
                'data' => null,
            ], 400);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function delete(Request $request, string $entityType, string $id, string $issuerId)
    {
        try {
            $input = $request->all();

            switch (strtoupper($entityType)) {
                case 'ORGANISATION':
                    $organisation = Organisation::where('id', $issuerId)->first();
                    $issuer = Issuer::where('id', $id)->first();

                    OrganisationHasIssuerApproval::where([
                        'organisation_id' => $organisation->id,
                        'issuer_id' => $issuer->id,
                    ])->delete();

                    return response()->json([
                        'message' => 'success',
                        'data' => null,
                    ]);
                    break;
                case 'RESEARCHER':
                    $user = User::where('id', $id)->first();
                    $issuer = Issuer::where('id', $issuerId)->first();

                    UserHasIssuerApproval::where([
                        'user_id' => $user->id,
                        'issuer_id' => $issuer->id,
                    ])->delete();

                    return response()->json([
                        'message' => 'success',
                        'data' => null,
                    ]);
                    break;
                default:
                    return response()->json([
                        'message' => 'unknown operation',
                        'data' => null,
                    ]);
                    break;
            }

            return response()->json([
                'message' => 'unknown request',
                'data' => null,
            ], 400);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }
}
