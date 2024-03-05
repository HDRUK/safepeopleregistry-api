<?php

namespace App\Http\Controllers\Api\V1;

use Exception;

use App\Models\Affiliation;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Exception\NotFoundException;

class AffiliationController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/training",
     *      summary="Return a list of Affiliations",
     *      description="Return a list of Affiliations",
     *      tags={"Affiliation"},
     *      summary="Affiliation@index",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="integer", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="Affiliation Name"),
     *                  @OA\Property(property="address_1", type="string", example="123 Road"),
     *                  @OA\Property(property="address_2", type="string", example="Address Two"),
     *                  @OA\Property(property="town", type="string", example="Town"),
     *                  @OA\Property(property="county", type="string", example="County"),
     *                  @OA\Property(property="country", type="string", example="Country"),
     *                  @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *                  @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *                  @OA\Property(property="verified", type="boolean", example="true"),
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
        $affiliations = Affiliation::all();
        
        return response()->json([
            'message' => 'success',
            'data' => $affiliations,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/training/{id}",
     *      summary="Return an Affiliation entry by ID",
     *      description="Return an Affiliation entry by ID",
     *      tags={"Affiliation"},
     *      summary="Affiliation@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Affiliation entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Affiliation entry ID",
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
     *                  @OA\Property(property="updated_at", type="integer", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="Affiliation Name"),
     *                  @OA\Property(property="address_1", type="string", example="123 Road"),
     *                  @OA\Property(property="address_2", type="string", example="Address Two"),
     *                  @OA\Property(property="town", type="string", example="Town"),
     *                  @OA\Property(property="county", type="string", example="County"),
     *                  @OA\Property(property="country", type="string", example="Country"),
     *                  @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *                  @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *                  @OA\Property(property="verified", type="boolean", example="true"),
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
        $affiliation = Affiliation::findOrFail($id);
        if ($affiliation) {
            return response()->json([
                'message' => 'success',
                'data' => $affiliation,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/affiliations",
     *      summary="Create an Affiliation entry",
     *      description="Create a Affiliation entry",
     *      tags={"Affiliation"},
     *      summary="Affiliation@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Affiliation definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Affiliation Name"),
     *              @OA\Property(property="address_1", type="string", example="123 Road"),
     *              @OA\Property(property="address_2", type="string", example="Address Two"),
     *              @OA\Property(property="town", type="string", example="Town"),
     *              @OA\Property(property="county", type="string", example="County"),
     *              @OA\Property(property="country", type="string", example="Country"),
     *              @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *              @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *              @OA\Property(property="verified", type="boolean", example="true"),
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
            $affiliation = Affiliation::create([
                'name' => $input['name'],
                'address_1' => $input['address_1'],
                'address_2' => $input['address_2'],
                'town' => $input['town'],
                'county' => $input['county'],
                'country' => $input['country'],
                'postcode' => $input['postcode'],
                'delegate' => $input['delegate'],
                'verified' => $input['verified'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $affiliation->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/affiliations/{id}",
     *      summary="Update an Affiliation entry",
     *      description="Update a Affiliation entry",
     *      tags={"Affiliation"},
     *      summary="Affiliation@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Affiliation entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Affiliation entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Affiliation definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="integer", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="name", type="string", example="Affiliation Name"),
     *              @OA\Property(property="address_1", type="string", example="123 Road"),
     *              @OA\Property(property="address_2", type="string", example="Address Two"),
     *              @OA\Property(property="town", type="string", example="Town"),
     *              @OA\Property(property="county", type="string", example="County"),
     *              @OA\Property(property="country", type="string", example="Country"),
     *              @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *              @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *              @OA\Property(property="verified", type="boolean", example="true"),
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
     *                  @OA\Property(property="updated_at", type="integer", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="Affiliation Name"),
     *                  @OA\Property(property="address_1", type="string", example="123 Road"),
     *                  @OA\Property(property="address_2", type="string", example="Address Two"),
     *                  @OA\Property(property="town", type="string", example="Town"),
     *                  @OA\Property(property="county", type="string", example="County"),
     *                  @OA\Property(property="country", type="string", example="Country"),
     *                  @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *                  @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *                  @OA\Property(property="verified", type="boolean", example="true"),
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
            Affiliation::where('id', $id)->update([
                'name' => $input['name'],
                'address_1' => $input['address_1'],
                'address_2' => $input['address_2'],
                'town' => $input['town'],
                'county' => $input['county'],
                'country' => $input['country'],
                'postcode' => $input['postcode'],
                'delegate' => $input['delegate'],
                'verified' => $input['verified'],                
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Affiliation::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/affiliations/{id}",
     *      summary="Edit an Affiliation entry",
     *      description="Edit a Affiliation entry",
     *      tags={"Affiliation"},
     *      summary="Affiliation@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Affiliation entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Affiliation entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Affiliation definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="integer", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="name", type="string", example="Affiliation Name"),
     *              @OA\Property(property="address_1", type="string", example="123 Road"),
     *              @OA\Property(property="address_2", type="string", example="Address Two"),
     *              @OA\Property(property="town", type="string", example="Town"),
     *              @OA\Property(property="county", type="string", example="County"),
     *              @OA\Property(property="country", type="string", example="Country"),
     *              @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *              @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *              @OA\Property(property="verified", type="boolean", example="true"),
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
     *                  @OA\Property(property="updated_at", type="integer", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="Affiliation Name"),
     *                  @OA\Property(property="address_1", type="string", example="123 Road"),
     *                  @OA\Property(property="address_2", type="string", example="Address Two"),
     *                  @OA\Property(property="town", type="string", example="Town"),
     *                  @OA\Property(property="county", type="string", example="County"),
     *                  @OA\Property(property="country", type="string", example="Country"),
     *                  @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *                  @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *                  @OA\Property(property="verified", type="boolean", example="true"),
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
            Affiliation::where('id', $id)->update([
                'name' => $input['name'],
                'address_1' => $input['address_1'],
                'address_2' => $input['address_2'],
                'town' => $input['town'],
                'county' => $input['county'],
                'country' => $input['country'],
                'postcode' => $input['postcode'],
                'delegate' => $input['delegate'],
                'verified' => $input['verified'],                
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Affiliation::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/affiliations/{id}",
     *      summary="Delete an Affiliation entry from the system by ID",
     *      description="Delete an Affiliation entry from the system",
     *      tags={"Affiliation"},
     *      summary="Affiliation@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Affiliation entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Affiliation entry ID",
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
            Affiliation::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }
}
