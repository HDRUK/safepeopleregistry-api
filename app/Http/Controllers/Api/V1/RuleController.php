<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\Rule;
use App\Models\RuleHasCondition;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class RuleController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/rules",
     *      summary="Return a list of Rules",
     *      description="Return a list of Rules",
     *      tags={"Rule"},
     *      summary="Rules@index",
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
     *                  @OA\Property(property="deleted_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="My First Rule"),
     *                  @OA\Property(property="fn", type="string", example="countOf(__CONDITION_VALUE__)"),
     *                  @OA\Property(property="enabled", type="boolean", example=true)
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
        $rules = Rule::all();

        return response()->json([
            'message' => 'succcess',
            'data' => $rules,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/rules/{id}",
     *      summary="Return a Rule entry by ID",
     *      description="Return a Rule entry by ID",
     *      tags={"Rule"},
     *      summary="Rules@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Rule entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Rule entry ID",
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
     *                  @OA\Property(property="deleted_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="My First Rule"),
     *                  @OA\Property(property="fn", type="string", example="countOf(__CONDITION_VALUE__)"),
     *                  @OA\Property(property="enabled", type="boolean", example=true)
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
        try {
            $rule = Rule::findOrFail($id);
            return response()->json([
                'message' => 'success',
                'data' => $rule,
            ], 200);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/rules",
     *      summary="Create a Rule entry",
     *      description="Create a Rule entry",
     *      tags={"Rule"},
     *      summary="Rules@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Rule definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="My First Rule"),
     *              @OA\Property(property="fn", type="string", example="countOf(__CONDITION_VALUE__)"),
     *              @OA\Property(property="enabled", type="boolean", example=true)
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
     *                  @OA\Property(property="message", type="string", example="success"),
     *                  @OA\Property(property="data", type="integer", example="1")
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
            $rule = Rule::create([
                'name' => $input['name'],
                'fn' => $input['fn'],
                'enabled' => $input['enabled'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $rule->id,
            ], 201);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/rules/{id}",
     *      summary="Edit a Rule entry",
     *      description="Edit a Rule entry",
     *      tags={"Rule"},
     *      summary="Rules@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Rule entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Rule entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Rule definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="deleted_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="name", type="string", example="My First Rule"),
     *              @OA\Property(property="fn", type="string", example="countOf(__CONDITION_VALUE__)"),
     *              @OA\Property(property="enabled", type="boolean", example=true)
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
     *                  @OA\Property(property="deleted_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="My First Rule"),
     *                  @OA\Property(property="fn", type="string", example="countOf(__CONDITION_VALUE__)"),
     *                  @OA\Property(property="enabled", type="boolean", example=true)
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
            $rule = Rule::where('id', $id)->update([
                'name' => (isset($input['name']) ? $input['name'] : $rule->name),
                'fn' => (isset($input['fn']) ? $input['fn'] : $rule->fn),
                'enabled' => (isset($input['enabled']) ? $input['enabled'] : $rule->enabled),
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Rule::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/rules/{id}",
     *      summary="Update a Rule entry",
     *      description="Update a Rule entry",
     *      tags={"Rule"},
     *      summary="Rules@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Rule entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Rule entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Rule definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="deleted_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="name", type="string", example="My First Rule"),
     *              @OA\Property(property="fn", type="string", example="countOf(__CONDITION_VALUE__)"),
     *              @OA\Property(property="enabled", type="boolean", example=true)
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
     *                  @OA\Property(property="deleted_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="My First Rule"),
     *                  @OA\Property(property="fn", type="string", example="countOf(__CONDITION_VALUE__)"),
     *                  @OA\Property(property="enabled", type="boolean", example=true)
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
            $rule = Rule::where('id', $id)->update([
                'name' => $input['name'],
                'fn' => $input['fn'],
                'enabled' => $input['enabled'],
            ], 200);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/rules/{id}",
     *      summary="Delete a Rule entry from the system by ID",
     *      description="Delete a Rule entry from the system",
     *      tags={"Rule"},
     *      summary="Rules@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Rule entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Rule entry ID",
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
            Rule::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
