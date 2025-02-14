<?php

namespace App\Http\Controllers\Api\V1;

use TriggerEmail;
use Exception;
use App\Models\User;
use App\Models\Employment;
use App\Models\Registry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmploymentController extends Controller
{
    //Hide from swagger
    public function indexByRegistryId(Request $request, int $registryId): JsonResponse
    {
        $employments = Employment::where('registry_id', $registryId)->get();

        return response()->json([
            'message' => 'success',
            'data' => $employments,
        ], 200);
    }

    //Hide from swagger    
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

    //Hide from swagger    
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
                'ror' => isset($input['ror']) ? $input['ror'] : null,
                'registry_id' => $registry->id,
                'email' => $input['email'],
            ]);

            // upon registering a new employment with a professional email, we
            // need to send an email verification to this professional email address
            if (!in_array(env('APP_ENV'), ['testing', 'ci'])) {
                $input = [
                    'type' => 'EMPLOYMENT',
                    'to' => User::where('registry_id', $registry->id)->select('id')->first()['id'],
                    'identifier' => 'pro_email_verify',
                    'pro_email' => $input['email'],
                ];

                TriggerEmail::spawnEmail($input);
            }

            return response()->json([
                'message' => 'success',
                'data' => $employment->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger    
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
            $employment->email = $input['email'];

            if (!$employment->save()) {
                return response()->json([
                    'message' => 'failed',
                    'data' => null,
                    'error' => 'unable to save employment'
                ], 400);
            }

            return response()->json([
                'message' => 'success',
                'data' => $employment,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger    
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
            $employment->email = isset($input['email']) ?
                $input['email'] : $employment->email;

            if (!$employment->save()) {
                return response()->json([
                    'message' => 'failed',
                    'data' => null,
                    'error' => 'unable to save employment'
                ], 400);
            }

            return response()->json([
                'message' => 'success',
                'data' => $employment,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function verifyEmailForEmployment(Request $request, int $id, int $registryId): JsonResponse
    {
        try {
            $em = Employment::where([
                'id' => $id,
                'registry_id' => $registryId,
            ])->first();

            $em->email_verified = 1;

            if ($em->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => null,
            ], 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger    
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
