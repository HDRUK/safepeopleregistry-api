<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\History;
use App\Traits\CommonFunctions;
use Carbon\Carbon;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * History is immutable (simulated) in the sense that it can never
 * change. Therefore there are no update/edit or destroy methods
 * available wihin this controlelr.
 */
class HistoryController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/histories",
     *      summary="Return a list of Histories",
     *      description="Return a list of Histories",
     *      tags={"History"},
     *      summary="History@index",
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
     *                  @OA\Property(property="endorsement_id", type="integer", example="213"),
     *                  @OA\Property(property="infringement_id", type="integer", example="12"),
     *                  @OA\Property(property="project_id", type="integer", example="2"),
     *                  @OA\Property(property="access_key_id", type="integer", example="2"),
     *                  @OA\Property(property="custodian_identifier", type="string", example="ABCD1234FGHI56789")
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
        $histories = History::paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $histories,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/histories/{id}",
     *      summary="Return a History entry by ID",
     *      description="Return a History entry by ID",
     *      tags={"History"},
     *      summary="History@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="History entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="History entry ID",
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
     *                  @OA\Property(property="endorsement_id", type="integer", example="213"),
     *                  @OA\Property(property="infringement_id", type="integer", example="12"),
     *                  @OA\Property(property="project_id", type="integer", example="2"),
     *                  @OA\Property(property="access_key_id", type="integer", example="2"),
     *                  @OA\Property(property="custodian_identifier", type="string", example="ABCD1234FGHI56789")
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
        $history = History::findOrFail($id);
        if ($history) {
            return response()->json([
                'message' => 'success',
                'data' => $history,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/histories",
     *      summary="Create a History entry",
     *      description="Create a History entry",
     *      tags={"History"},
     *      summary="History@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="History definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="endorsement_id", type="integer", example="213"),
     *              @OA\Property(property="infringement_id", type="integer", example="12"),
     *              @OA\Property(property="project_id", type="integer", example="2"),
     *              @OA\Property(property="access_key_id", type="integer", example="2"),
     *              @OA\Property(property="custodian_identifier", type="string", example="ABCD1234FGHI56789")
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
     *                  @OA\Property(property="endorsement_id", type="integer", example="213"),
     *                  @OA\Property(property="infringement_id", type="integer", example="12"),
     *                  @OA\Property(property="project_id", type="integer", example="2"),
     *                  @OA\Property(property="access_key_id", type="integer", example="2"),
     *                  @OA\Property(property="custodian_identifier", type="string", example="ABCD1234FGHI56789")
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

            $ledger = [
                'endorsement_id' => $input['endorsement_id'],
                'infringement_id' => $input['infringement_id'],
                'project_id' => $input['project_id'],
                'access_key_id' => $input['access_key_id'],
                'custodian_identifier' => $input['custodian_identifier'],
                'history_entry_ts' => Carbon::now()->toDateTimeString(),
            ];

            $ledgerHash = Hash::make(json_encode($ledger));

            $history = History::create([
                'endorsement_id' => $input['endorsement_id'],
                'infringement_id' => $input['infringement_id'],
                'project_id' => $input['project_id'],
                'access_key_id' => $input['access_key_id'],
                'custodian_identifier' => $input['custodian_identifier'],
                'ledger_hash' => $ledgerHash,
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $history->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
