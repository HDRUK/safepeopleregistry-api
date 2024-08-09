<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\Employment;
use App\Models\Registry;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class EmploymentController extends Controller
{
    public function indexByRegistryId(Request $request, int $registryId): JsonResponse
    {
        $employments = Employment::where('registry_id', $registryId)->get();

        return response()->json([
            'message' => 'succes',
            'data' => $employments,
        ], 200);
    }

    public function showByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $employment = Employment::where([
                'id' => $id,
                'registry_id' => $registryId,
            ])->first();

            return response()->json([
                'message' => 'success',
                'data' => $employment,
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

            $employment = Employment::create([
                'employer_name' => $input['employer_name'],
                'from' => $input['from'],
                'to' => $input['to'],
                'is_current' => $input['is_current'],
                'department' => $input['department'],
                'role' => $input['role'],
                'employer_address' => $input['employer_address'],
                'ror' => $input['ror'],
                'registry_id' => $registry->id,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $employment->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $employment = Employment::where([
                'id' => $id,
                'registry_id' => $registryId,
            ])->first();

            $employment->employer_name = $input['employer_name'];
            $employment->from = $input['from'];
            $employment->to = $input['to'];
            $employment->is_current = $input['is_current'];
            $employment->department = $input['department'];
            $employment->role = $input['role'];
            $employment->employer_address = $input['employer_address'];
            $employment->ror = $input['ror'];

            if ($employment->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $employment,
                ], 200);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function editByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $employment = Employment::where([
                'id' => $id,
                'registry_id' => $registryId,
            ])->first();

            $employment->employer_name = isset($input['employer_name']) ?
                $input['employer_name'] : $employment->employer_name;
            $employment->from = isset($input['from']) ?
                $input['from'] : $employment->from;
            $employment->to = isset($input['to']) ?
                $input['to'] : $employment->to;
            $employment->is_current = isset($input['is_current']) ?
                $input['is_current'] : $employment->is_current;
            $employment->department = isset($input['department']) ?
                $input['department'] : $employment->department;
            $employment->role = isset($input['role']) ?
                $input['role'] : $employment->role;
            $employment->employer_address = isset($input['employer_address']) ?
                $input['employer_address'] : $employment->employer_address;
            $employment->ror = isset($input['ror']) ?
                $input['ror'] : $employment->ror;

            if ($employment->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $employment,
                ], 200);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function destroyByRegistryId(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            Employment::where([
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
