<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\Training;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Exception\NotFoundException;

class TrainingController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/training",
     *      summary="Return a list of Training entries",
     *      description="Return a list of Training entries",
     *      tags={"Training"},
     *      summary="Training@index",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="provider", type="string", example="ONS"),
     *                  @OA\Property(property="awarded_at", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="expires_at", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="expires_in_years", type="integer", example="2"),
     *                  @OA\Property(property="training_name", type="string", example="Safe Researcher Training")
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found"),
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $trainings = Training::all();

        return response()->json([
            'message' => 'success',
            'data' => $trainings,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/training/{id}",
     *      summary="Return a Training entry by ID",
     *      description="Return a Training entry by ID",
     *      tags={"Training"},
     *      summary="Training@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Training entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Training entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="provider", type="string", example="ONS"),
     *                  @OA\Property(property="awarded_at", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="expires_at", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="expires_in_years", type="integer", example="2"),
     *                  @OA\Property(property="training_name", type="string", example="Safe Researcher Training")
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found"),
     *          )
     *      )
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $trainings = Training::findOrFail($id);
        if ($trainings) {
            return response()->json([
                'message' => 'success',
                'data' => $trainings,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/training",
     *      summary="Create a Training entry",
     *      description="Create a Training entry",
     *      tags={"Training"},
     *      summary="Training@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Training definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="provider", type="string", example="ONS"),
     *              @OA\Property(property="awarded_at", type="string", example="2024-02-04 12:10:00"),
     *              @OA\Property(property="expires_at", type="string", example="2026-02-04 12:09:59"),
     *              @OA\Property(property="expires_in_years", type="integer", example="2"),
     *              @OA\Property(property="training_name", type="string", example="Safe Researcher Training")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="provider", type="string", example="ONS"),
     *                  @OA\Property(property="awarded_at", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="expires_at", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="expires_in_years", type="integer", example="2"),
     *                  @OA\Property(property="training_name", type="string", example="Safe Researcher Training")
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();
            $training = Training::create([
                'registry_id' => $input['registry_id'],
                'provider' => $input['provider'],
                'awarded_at' => $input['awarded_at'],
                'expires_at' => $input['expires_at'],
                'expires_in_years' => $input['expires_in_years'],
                'training_name' => $input['training_name'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $training->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/training/{id}",
     *      summary="Update a Training entry",
     *      description="Update a Training entry",
     *      tags={"Training"},
     *      summary="Training@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Training entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Training entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Training definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="provider", type="string", example="ONS"),
     *              @OA\Property(property="awarded_at", type="string", example="2024-02-04 12:10:00"),
     *              @OA\Property(property="expires_at", type="string", example="2026-02-04 12:09:59"),
     *              @OA\Property(property="expires_in_years", type="integer", example="2"),
     *              @OA\Property(property="training_name", type="string", example="Safe Researcher Training")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="provider", type="string", example="ONS"),
     *                  @OA\Property(property="awarded_at", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="expires_at", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="expires_in_years", type="integer", example="2"),
     *                  @OA\Property(property="training_name", type="string", example="Safe Researcher Training")
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            Training::where('id', $id)->update([
                'registry_id' => $input['registry_id'],
                'provider' => $input['id'],
                'awarded_at' => $input['awarded_at'],
                'expires_at' => $input['expires_at'],
                'expires_in_years' => $input['expires_in_years'],
                'training_name' => $input['training_name'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Training::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/training/{id}",
     *      summary="Edit a Training entry",
     *      description="Edit a Training entry",
     *      tags={"Training"},
     *      summary="Training@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Training entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Training entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Training definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="provider", type="string", example="ONS"),
     *              @OA\Property(property="awarded_at", type="string", example="2024-02-04 12:10:00"),
     *              @OA\Property(property="expires_at", type="string", example="2026-02-04 12:09:59"),
     *              @OA\Property(property="expires_in_years", type="integer", example="2"),
     *              @OA\Property(property="training_name", type="string", example="Safe Researcher Training")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="provider", type="string", example="ONS"),
     *                  @OA\Property(property="awarded_at", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="expires_at", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="expires_in_years", type="integer", example="2"),
     *                  @OA\Property(property="training_name", type="string", example="Safe Researcher Training")
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function edit(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            Training::where('id', $id)->update([
                'registry_id' => $input['registry_id'],
                'provider' => $input['provider'],
                'awarded_at' => $input['awarded_at'],
                'expires_at' => $input['expires_at'],
                'expires_in_years' => $input['expires_in_years'],
                'training_name' => $input['training_name'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Training::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/training/{id}",
     *      summary="Delete a training entry from the system by ID",
     *      description="Delete a training entry from the system",
     *      tags={"Training"},
     *      summary="Training@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Training entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Training entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *           ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            Training::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
