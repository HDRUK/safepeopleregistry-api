<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Issuer;
use App\Models\Organisation;
use App\Models\UserHasIssuerApproval;
use App\Models\OrganisationHasIssuerApproval;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;

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
                case 'USER':
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
}
