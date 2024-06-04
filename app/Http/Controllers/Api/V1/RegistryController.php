<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\Registry;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Exception\NotFoundException;

class RegistryController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/registry",
     *      summary="Return a list of Registry entries",
     *      description="Return a list of Registry entries",
     *      tags={"Registry"},
     *      summary="Registry@index",
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
     *                  @OA\Property(property="user_id", type="integer", example="243"),
     *                  @OA\Property(property="verified", type="boolean", example=true),
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
        $registries = Registry::with(
            [
                'files'
            ]
        )->get();

        return response()->json([
            'message' => 'success',
            'data' => $registries,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/registry/{id}",
     *      summary="Return a Registry entry by ID",
     *      description="Return a Registry entry by ID",
     *      tags={"Registry"},
     *      summary="Registry@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry entry ID",
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
     *                  @OA\Property(property="user_id", type="integer", example="243"),
     *                  @OA\Property(property="verified", type="boolean", example=true),
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
        $registries = Registry::with(
            [
                'files',
            ]
        )->findOrFail($id);
        if ($registries) {
            return response()->json([
                'message' => 'success',
                'data' => $registries,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/registry",
     *      summary="Create a Registry entry",
     *      description="Create a Registry entry",
     *      tags={"Registry"},
     *      summary="Registry@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Registry definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="integer", example="1"),
     *              @OA\Property(property="dl_ident", type="string", example="134157839"),
     *              @OA\Property(property="pp_ident", type="string", example="HSJFY785615630X99 123"),
     *              @OA\Property(property="verified", type="boolean", example="true")
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
     *                  @OA\Property(property="user_id", type="integer", example="1"),
     *                  @OA\Property(property="dl_ident", type="string", example="134157839"),
     *                  @OA\Property(property="pp_ident", type="string", example="HSJFY785615630X99 123"),
     *                  @OA\Property(property="verified", type="boolean", example="true")
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

            $registry = Registry::create([
                'user_id' => $input['user_id'],
                'dl_ident' => $input['dl_ident'],
                'pp_ident' => $input['pp_ident'],
                'verified' => $input['verified'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $registry->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/registry/{id}",
     *      summary="Update a Registry entry",
     *      description="Update a Registry entry",
     *      tags={"Registry"},
     *      summary="Registry@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Registry definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="user_id", type="integer", example="1"),
     *              @OA\Property(property="dl_ident", type="string", example="134157839"),
     *              @OA\Property(property="pp_ident", type="string", example="HSJFY785615630X99 123"),
     *              @OA\Property(property="verified", type="boolean", example="true")
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
     *                  @OA\Property(property="user_id", type="integer", example="1"),
     *                  @OA\Property(property="dl_ident", type="string", example="134157839"),
     *                  @OA\Property(property="pp_ident", type="string", example="HSJFY785615630X99 123"),
     *                  @OA\Property(property="verified", type="boolean", example="true")
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

            Registry::where('id', $id)->update([
                'user_id' => $input['user_id'],
                'dl_ident' => $input['dl_ident'],
                'pp_ident' => $input['pp_ident'],
                'verified' => $input['verified'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Registry::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/registry/{id}",
     *      summary="Edit a Registry entry",
     *      description="Edit a Registry entry",
     *      tags={"Registry"},
     *      summary="Registry@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Registry definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="user_id", type="integer", example="1"),
     *              @OA\Property(property="dl_ident", type="string", example="134157839"),
     *              @OA\Property(property="pp_ident", type="string", example="HSJFY785615630X99 123"),
     *              @OA\Property(property="verified", type="boolean", example="true")
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
     *                  @OA\Property(property="user_id", type="integer", example="1"),
     *                  @OA\Property(property="dl_ident", type="string", example="134157839"),
     *                  @OA\Property(property="pp_ident", type="string", example="HSJFY785615630X99 123"),
     *                  @OA\Property(property="verified", type="boolean", example="true")
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

            Registry::where('id', $id)->update([
                'user_id' => $input['user_id'],
                'dl_ident' => $input['dl_ident'],
                'pp_ident' => $input['pp_ident'],
                'verified' => $input['verified'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Registry::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/registry/{id}",
     *      summary="Delete a Registry entry from the system by ID",
     *      description="Delete a Registry entry from the system",
     *      tags={"Registry"},
     *      summary="Registry@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry entry ID",
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
            Registry::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
