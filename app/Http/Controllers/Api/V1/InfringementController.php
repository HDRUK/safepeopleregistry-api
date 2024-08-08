<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Infringement;
use App\Traits\CommonFunctions;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfringementController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/infringements",
     *      summary="Return a list of Infringements",
     *      description="Return a list of Infringements",
     *      tags={"Infringement"},
     *      summary="Infringement@index",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="reported_by", type="integer", example="1"),
     *                  @OA\Property(property="comment", type="string", example="Infringement detected"),
     *                  @OA\Property(property="raised_against", type="integer", example="12"),
     *              )
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="not found"),
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $infringements = Infringement::paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $infringements,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/infringements/{id}",
     *      summary="Return an Infringement entry by ID",
     *      description="Return an Infringement entry by ID",
     *      tags={"Infringement"},
     *      summary="Infringement@show",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Infringement entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Infringement entry ID",
     *         ),
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="reported_by", type="integer", example="1"),
     *                  @OA\Property(property="comment", type="string", example="Infringement detected"),
     *                  @OA\Property(property="raised_against", type="integer", example="12")
     *              )
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="not found"),
     *          )
     *      )
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $infringement = Infringement::findOrFail($id);
        if ($infringement) {
            return response()->json([
                'message' => 'success',
                'data' => $infringement,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/infringements",
     *      summary="Create an Infringement entry",
     *      description="Create an Infringement entry",
     *      tags={"Infringement"},
     *      summary="Infringement@store",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Infringement definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="reported_by", type="integer", example="1"),
     *              @OA\Property(property="comment", type="string", example="Infringement detected"),
     *              @OA\Property(property="raised_against", type="integer", example="12"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="reported_by", type="integer", example="1"),
     *                  @OA\Property(property="comment", type="string", example="Infringement detected"),
     *                  @OA\Property(property="raised_against", type="integer", example="12"),
     *              )
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();
            $infringement = Infringement::create([
                'reported_by' => $input['reported_by'],
                'comment' => $input['comment'],
                'raised_against' => $input['raised_against'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $infringement->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
