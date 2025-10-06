<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Carbon\Carbon;
use App\Models\Registry;
use App\Models\Education;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Educations\GetEducationByRegistry;
use App\Http\Requests\Educations\CreateEducationByRegistry;
use App\Http\Requests\Educations\GetEducationByIdByRegistry;
use App\Http\Requests\Educations\DeleteEducationByIdByRegistry;
use App\Http\Requests\Educations\UpdateEducationByIdByRegistry;

/**
 * @OA\Tag(
 *     name="Education",
 *     description="API endpoints for managing education records"
 * )
 */
class EducationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/educations/registries/{registryId}",
     *     tags={"Education"},
     *     summary="Get education records by registry ID",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Education")
     *         )
     *     ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
     *      )
     * )
     */
    public function indexByRegistryId(GetEducationByRegistry $request, int $registryId): JsonResponse
    {
        $educations = Education::where('registry_id', $registryId)->get();

        return response()->json([
            'message' => 'success',
            'data' => $educations,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/educations/{id}/registries/{registryId}",
     *     tags={"Education"},
     *     summary="Get a specific education record by ID and registry ID",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the education record",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Education")
     *     ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
     *      ),
     *     @OA\Response(
     *         response=404,
     *         description="Education record not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Education record not found")
     *         )
     *     )
     * )
     */
    public function showByRegistryId(GetEducationByIdByRegistry $request, int $id, int $registryId): JsonResponse
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

    /**
     * @OA\Post(
     *     path="/api/v1/registries/{registryId}/educations",
     *     tags={"Education"},
     *     summary="Create a new education record for a registry",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Education")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *         )
     *     )
     * )
     */
    public function storeByRegistryId(CreateEducationByRegistry $request, int $registryId): JsonResponse
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

    /**
     * @OA\Put(
     *     path="/api/v1/registries/{registryId}/educations/{id}",
     *     tags={"Education"},
     *     summary="Update an existing education record",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the education record",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Education")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(ref="#/components/schemas/Education")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *         )
     *     )
     * )
     */
    public function updateByRegistryId(UpdateEducationByIdByRegistry $request, int $id, int $registryId): JsonResponse
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

    /**
     * @OA\Delete(
     *     path="/api/v1/registries/{registryId}/educations/{id}",
     *     tags={"Education"},
     *     summary="Delete an education record",
     *     @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         required=true,
     *         description="ID of the registry",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the education record",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="success"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid argument(s)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *         )
     *     )
     * )
     */
    public function destroyByRegistryId(DeleteEducationByIdByRegistry $request, int $id, int $registryId): JsonResponse
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
