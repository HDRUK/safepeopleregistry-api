<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Endorsement;
use App\Traits\CommonFunctions;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/endorsements",
     *      summary="Return a list of Endorsements",
     *      description="Return a list of Endorsements",
     *      tags={"Endorsement"},
     *      summary="Endorsement@index",
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
     *                  @OA\Property(property="reported_by", type="integer", example="1"),
     *                  @OA\Property(property="comment", type="string", example="Endorsement given"),
     *                  @OA\Property(property="raised_against", type="integer", example="12")
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $endorsements = Endorsement::paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $endorsements,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/endorsements/{id}",
     *      summary="Return an Endorsement entry by ID",
     *      description="Return an Endorsement entry by ID",
     *      tags={"Endorsement"},
     *      summary="Endorsement@show",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Endorsement entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Endorsement entry ID",
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
     *                  @OA\Property(property="comment", type="string", example="Endorsement given"),
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
        $endorsement = Endorsement::findOrFail($id);
        if ($endorsement) {
            return response()->json([
                'message' => 'success',
                'data' => $endorsement,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/endorsements",
     *      summary="Create an Endorsements entry",
     *      description="Create an Endorsements entry",
     *      tags={"Endorsements"},
     *      summary="Endorsements@store",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Endorsements definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="reported_by", type="integer", example="1"),
     *              @OA\Property(property="comment", type="string", example="Endorsement given"),
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
            $endorsement = Endorsement::create([
                'reported_by' => $input['reported_by'],
                'comment' => $input['comment'],
                'raised_against' => $input['raised_against'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $endorsement->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
