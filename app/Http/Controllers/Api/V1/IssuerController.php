<?php

namespace App\Http\Controllers\Api\V1;

use Hash;
use Exception;

use App\Models\Issuer;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Exception\NotFoundException;


class IssuerController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/issuers",
     *      summary="Return a list of Issuers",
     *      description="Return a list of Issuers",
     *      tags={"Issuer"},
     *      summary="Issuer@index",
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
     *                  @OA\Property(property="name", type="string", example="An Issuer"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
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
        $issuers = Issuer::all();
        
        return response()->json([
            'message' => 'success',
            'data' => $issuers,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/issuers/{id}",
     *      summary="Return an Issuer entry by ID",
     *      description="Return an Issuer entry by ID",
     *      tags={"Issuer"},
     *      summary="Issuer@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Issuer ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Issuer ID",
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
     *                  @OA\Property(property="name", type="string", example="An Issuer"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
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
        $issuer = Issuer::findOrFail($id);
        if ($issuer) {
            return response()->json([
                'message' => 'success',
                'data' => $issuer,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Get(
     *      path="/api/v1/issuers/identifier/{id}",
     *      summary="Return an Issuer entry by Unique Identifier",
     *      description="Return an Issuer entry by Unique Identifier",
     *      tags={"Issuer"},
     *      summary="Issuer@showByUniqueIdentifier",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Issuer Unique Identifier",
     *         required=true,
     *         example="c3eddb33-db74-4ea7-961a-778740f17e25",
     *         @OA\Schema(
     *            type="string",
     *            description="Issuer Unique Identifier",
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
     *                  @OA\Property(property="name", type="string", example="An Issuer"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
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
    public function showByUniqueIdentifier(Request $request, string $id): JsonResponse
    {
        $issuer = Issuer::where('unique_identifier', $id)->first();
        if ($issuer) {
            return response()->json([
                'message' => 'success',
                'data' => $issuer,
            ], 200);
        }

        throw new NotFoundException();
    }
    
    /**
     * @OA\Post(
     *      path="/api/v1/issuers",
     *      summary="Create an Issuer entry",
     *      description="Create a Issuer entry",
     *      tags={"Issuer"},
     *      summary="Issuer@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Issuer definition",
     *          @OA\JsonContent(  
     *                  @OA\Property(property="name", type="string", example="An Issuer"),
     *                  @OA\Property(property="enabled", type="boolean", example="true")
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
     *                  @OA\Property(property="name", type="string", example="An Issuer"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
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

            $signature = Str::random(40);
            $calculatedHash = Hash::make($signature . 
                 ':' . env('ISSUER_SALT_1') .
                 ':' . env('ISSUER_SALT_2')
            );

            $issuer = Issuer::create([
                'name' => $input['name'],
                'unique_identifier' => $signature,
                'calculated_hash' => $calculatedHash,
                'contact_email' => $input['contact_email'],
                'enabled' => $input['enabled'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $issuer->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/issuers/{id}",
     *      summary="Edit an Issuer entry",
     *      description="Edit a Issuer entry",
     *      tags={"Issuer"},
     *      summary="Issuer@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Issuer ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Issuer ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Issuer definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="An Issuer"),
     *              @OA\Property(property="enabled", type="boolean", example="true")
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
     *                  @OA\Property(property="name", type="string", example="An Issuer"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
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

            $issuer = Issuer::where('id', $id)->first();
            $issuer->name = $input['name'];
            $issuer->contact_email = isset($input['contact_email']) ? $input['contact_email'] : $issuer->contact_email;
            $issuer->enabled = $input['enabled'];
            if ($issuer->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $issuer,
                ], 200);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/issuers/{id}",
     *      summary="Edit an Issuer entry",
     *      description="Edit a Issuer entry",
     *      tags={"Issuer"},
     *      summary="Issuer@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Issuer ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Issuer ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Issuer definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="An Issuer"),
     *              @OA\Property(property="enabled", type="boolean", example="true")
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
     *                  @OA\Property(property="name", type="string", example="An Issuer"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
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

            $issuer = Issuer::where('id', $id)->first();
            $issuer->name = $input['name'];
            $issuer->contact_email = isset($input['contact_email']) ? $input['contact_email'] : $issuer->contact_email;
            $isser->enabled = $input['enabled'];
            if ($issuer->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $issuer,
                ], 200);
            } 
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/issuers/{id}",
     *      summary="Delete an Issuer entry from the system by ID",
     *      description="Delete an Issuer entry from the system",
     *      tags={"Issuer"},
     *      summary="Issuer@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Issuer entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Issuer entry ID",
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
            Issuer::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Stub function for next ticket item
     */
    public function push(Request $request): JsonResponse
    {
        try {
            // Only for test passing. Actual code is split into another ticket
            return response()->json([
                'message' => 'success',
                'data' => $request->all()
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
