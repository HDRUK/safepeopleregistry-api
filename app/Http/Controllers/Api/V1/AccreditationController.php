<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;

use App\Models\Accreditation;
use App\Models\RegistryHasAccreditation;

use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Traits\CommonFunctions;

class AccreditationController extends Controller
{
    use CommonFunctions;

    public function indexByRegistryId(Request $request, int $registryId): JsonResponse
    {
        $rha = RegistryHasAccreditation::where('registry_id', $registryId)
            ->get()
            ->pluck('accreditation_id');

        $accreditations = Accreditation::whereIn('id', $rha)
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $accreditations,
        ], 200);
    }

    public function storeByRegistryId(Request $request, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $accreditation = Accreditation::create([
                'awarded_at' => Carbon::parse($input['awarded_at'])->toDateString(),
                'awarding_body_name' => $input['awarding_body_name'],
                'awarding_body_ror' => isset($input['awarding_body_ror']) ?? '',
                'title' => $input['title'],
                'expires_at' => Carbon::parse($input['expires_at'])->toDateString(),
                'awarded_locale' => $input['awarded_locale'],
            ]);

            RegistryHasAccreditation::create([
                'registry_id' => $registryId,
                'accreditation_id' => $accreditation->id,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $accreditation->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();
            $accreditation = Accreditation::where('id', $id)->first();

            $accreditation->awarded_at = Carbon::parse($input['awarded_at'])->toDateString();
            $accreditation->awarding_body_name = $input['awarding_body_name'];
            $accreditation->awarding_body_ror = $input['awarding_body_ror'];
            $accreditation->title = $input['title'];
            $accreditation->expires_at = Carbon::parse($input['expires_at'])->toDateString();
            $accreditation->awarded_locale = $input['awarded_locale'];

            $accreditation->save();

            return response()->json([
                'message' => 'success',
                'data' => $accreditation,
            ], 200);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function editByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();
            $accreditation = Accreditation::where('id', $id)->first();

            $accreditation->awarded_at = isset($input['awarded_at']) ?
                Carbon::parse($input['awarded_at'])->toDateString() : $accreditation->awarded_at;
            $accreditation->awarding_body_name = isset($input['awarding_body_name']) ?
                $input['awarding_body_name'] : $accreditation->awarding_body_name;
            $accreditation->awarding_body_ror = isset($input['awarding_body_ror']) ?
                $input['awarding_body_ror'] : $accreditation->awarding_body_ror;
            $accreditation->title = isset($input['title']) ?
                $input['title'] : $accreditation->title;
            $accreditation->expires_at = isset($input['expires_at']) ?
                Carbon::parse($input['expires_at'])->toDateString() : $accreditation->expires_at;
            $accreditation->awarded_locale = isset($input['awarded_locale']) ?
                $input['awarded_locale'] : $accreditation->awarded_locale;

            $accreditation->save();

            return response()->json([
                'message' => 'success',
                'data' => $accreditation,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function destroyByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            Accreditation::where('id', $id)->first()->delete();
            RegistryHasAccreditation::where([
                'accreditation_id' => $id,
                'registry_id' => $registryId,
            ])->delete();

            return response()->json([
                'message' => 'success',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
