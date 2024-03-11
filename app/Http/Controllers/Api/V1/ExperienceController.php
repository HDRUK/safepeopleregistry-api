<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\Experience;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Exception\NotFoundException;

class ExperienceController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/experiences",
     *      summary="Return a list of Experience entries",
     *      description="Return a list of Experience entries",
     *      tags={"Experience"},
     *      summary="Experience@index",
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
     *                  @OA\Property(property="project_id", type="integer", example="1"),
     *                  @OA\Property(property="from", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="to", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="affiliation_id", type="integer", example="2")
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
        $experiences = Experience::all();

        return response()->json([
            'message' => 'success',
            'data' => $experiences,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/experiences/{id}",
     *      summary="Return an Experience entry by ID",
     *      description="Return an Experience entry by ID",
     *      tags={"Experience"},
     *      summary="Experience@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Experience entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Experience entry ID",
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
     *                  @OA\Property(property="project_id", type="integer", example="1"),
     *                  @OA\Property(property="from", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="to", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="affiliation_id", type="integer", example="2")
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
        $experience = Experience::findOrFailt($id);
        if ($experience) {
            return response()->json([
                'message' => 'success',
                'data' => $experience,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/experiences",
     *      summary="Create an Experience entry",
     *      description="Create an Experience entry",
     *      tags={"Experience"},
     *      summary="Experience@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Experience definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="project_id", type="integer", example="1"),
     *              @OA\Property(property="from", type="string", example="2024-02-04 12:10:00"),
     *              @OA\Property(property="to", type="string", example="2026-02-04 12:09:59"),
     *              @OA\Property(property="affiliation_id", type="integer", example="2")
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
            $experience = Experience::create([
                'project_id' => $input['project_id'],
                'from' => $input['from'],
                'to' => $input['to'],
                'affiliation_id' => $input['affiliation_id'],
            ]);

            return resposne()->json([
                'message' => 'success',
                'data' => $experience->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/experiences/{id}",
     *      summary="Update an Experience entry",
     *      description="Update an Experience entry",
     *      tags={"Experience"},
     *      summary="Experience@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Experience entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Experience entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Experience definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="project_id", type="integer", example="1"),
     *              @OA\Property(property="from", type="string", example="2024-02-04 12:10:00"),
     *              @OA\Property(property="to", type="string", example="2026-02-04 12:09:59"),
     *              @OA\Property(property="affiliation_id", type="integer", example="2")
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
     *                  @OA\Property(property="project_id", type="integer", example="1"),
     *                  @OA\Property(property="from", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="to", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="affiliation_id", type="integer", example="2")
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

            Experience::where('id', $id)->update([
                'project_id' => $input['project_id'],
                'from' => $input['from'],
                'to' => $input['to'],
                'affiliation_id' => $input['affiliation_id'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Experience::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/experiences/{id}",
     *      summary="Edit an Experience entry",
     *      description="Edit an Experience entry",
     *      tags={"Experience"},
     *      summary="Experience@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Experience entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Experience entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Experience definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="project_id", type="integer", example="1"),
     *              @OA\Property(property="from", type="string", example="2024-02-04 12:10:00"),
     *              @OA\Property(property="to", type="string", example="2026-02-04 12:09:59"),
     *              @OA\Property(property="affiliation_id", type="integer", example="2")
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
     *                  @OA\Property(property="project_id", type="integer", example="1"),
     *                  @OA\Property(property="from", type="string", example="2024-02-04 12:10:00"),
     *                  @OA\Property(property="to", type="string", example="2026-02-04 12:09:59"),
     *                  @OA\Property(property="affiliation_id", type="integer", example="2")
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

            Experience::where('id', $id)->update([
                'project_id' => $input['project_id'],
                'from' => $input['from'],
                'to' => $input['to'],
                'affiliation_id' => $input['affiliation_id'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Experience::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/experiences/{id}",
     *      summary="Delete an Experience entry from the system by ID",
     *      description="Delete a Experience entry from the system",
     *      tags={"Experience"},
     *      summary="Experience@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Experience entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Experience entry ID",
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
            Experience::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
