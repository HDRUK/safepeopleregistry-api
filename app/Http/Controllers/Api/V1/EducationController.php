<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Carbon\Carbon;
use App\Models\Education;
use App\Models\Registry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EducationController extends Controller
{
    public function indexByRegistryId(Request $request, int $registryId): JsonResponse
    {
        $educations = Education::where('registry_id', $registryId)->get();

        return response()->json([
            'message' => 'success',
            'data' => $educations,
        ], 200);
    }

    public function showByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $education = Education::where([
                'id' => $id,
                'registry_id' => $registryId,
            ])->first();

            return response()->json([
                'message' => 'success',
                'data' => $education,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function storeByRegistryId(Request $request, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $registry = Registry::where('id', $registryId)->first();
            if (!$registry) {
                return response()->json([
                    'message' => 'failed',
                    'data' => null,
                    'error' => 'registry not found',
                ], 400);
            }

            $education = Education::create([
                'title' => $input['title'],
                'from' => Carbon::parse($input['from'])->toDateString(),
                'to' => Carbon::parse($input['to'])->toDateString(),
                'institute_name' => $input['institute_name'],
                'institute_address' => $input['institute_address'],
                'institute_identifier' => $input['institute_identifier'],
                'source' => $input['source'],
                'registry_id' => $registry->id,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $education->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $education = Education::where([
                'id' => $id,
                'registry_id' => $registryId,
            ])->first();

            $education->title = $input['title'];
            $education->from = Carbon::parse($input['from'])->toDateString();
            $education->to = Carbon::parse($input['to'])->toDateString();
            $education->institute_name = $input['institute_name'];
            $education->institute_address = $input['institute_address'];
            $education->institute_identifier = $input['institute_identifier'];
            $education->source = $input['source'];
            $education->registry_id = $input['registry_id'];

            if (!$education->save()) {
                return response()->json([
                    'message' => 'failed',
                    'data' => null,
                    'error' => 'unable to save education',
                ], 400);
            }

            return response()->json([
                'message' => 'success',
                'data' => $education,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function editByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $education = Education::where([
                'id' => $id,
                'registry_id' => $registryId,
            ])->first();

            $education->title = isset($input['title']) ?
                $input['title'] : $education->title;
            $education->from = isset($input['from']) ?
                Carbon::parse($input['from'])->toDateString() : $education->from;
            $education->to = isset($input['to']) ?
                Carbon::parse($input['to'])->toDateString() : $education->to;
            $education->institute_name = isset($input['institute_name']) ?
                $input['institute_name'] : $education->institute_name;
            $education->institute_address = isset($input['institute_address']) ?
                $input['institute_address'] : $education->institute_address;
            $education->institute_identifier = isset($input['institute_identifier']) ?
                $input['institute_identifier'] : $education->institute_identifier;
            $education->source = isset($input['source']) ?
                $input['source'] : $education->source;
            $education->registry_id = isset($input['registry_id']) ?
                $input['registry_id'] : $education->registry_id;

            if (!$education->save()) {
                return response()->json([
                    'message' => 'failed',
                    'data' => null,
                    'error' => 'unable to save education',
                ], 400);
            }

            return response()->json([
                'message' => 'success',
                'data' => $education,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function destroyByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            Education::where([
                'id' => $id,
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