<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\Resolution;
use App\Models\Infringement;
use App\Models\InfringementHasResolution;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\CommonFunctions;

class ResolutionController extends Controller
{
    use CommonFunctions;

    public function indexByRegistryId(Request $request, int $registryId): JsonResponse
    {
        $resolutions = Resolution::where('registry_id', $registryId)->get();

        return response()->json([
            'message' => 'success',
            'data' => $resolutions,
        ], 200);
    }

    public function storeByRegistryId(Request $request, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $resolution = Resolution::create([
                'comment' => $input['comment'],
                'custodian_by' => $input['custodian_by'],
                'registry_id' => $registryId,
                'resolved' => $input['resolved'],
            ]);

            if (isset($input['infringement_id'])) {
                $infringement = Infringement::where('id', $input['infringement_id'])->first();

                if ($infringement) {
                    InfringementHasResolution::create([
                        'resolution_id' => $resolution->id,
                        'infringement_id' => $infringement->id,
                    ]);
                }
            }

            return response()->json([
                'message' => 'success',
                'data' => $resolution->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
