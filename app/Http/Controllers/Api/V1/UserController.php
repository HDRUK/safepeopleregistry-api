<?php

namespace App\Http\Controllers\Api\V1;

use Hash;
use Exception;

use App\Models\User;
use App\Models\UserHasIssuerApproval;
use App\Models\UserHasIssuerPermission;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;

use App\Exceptions\NotFoundException;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/users",
     *      summary="Return a list of Users",
     *      description="Return a list of Users",
     *      tags={"User"},
     *      summary="User@index",
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
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
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
        $users = User::with([
            'permissions'
        ])->get();

        return response()->json([
            'message' => 'success',
            'data' => $users,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/users/{id}",
     *      summary="Return a User entry by ID",
     *      description="Return a User entry by ID",
     *      tags={"User"},
     *      summary="User@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="User ID",
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
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
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
            $user = User::with([
                'permissions'
            ])->findOrFail($id);
            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            throw new NotFoundException();
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/users",
     *      summary="Create a User entry",
     *      description="Create a User entry",
     *      tags={"Users"},
     *      summary="Users@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="User definition",
     *          @OA\JsonContent(  
     *                  @OA\Property(property="name", type="string", example="An Issuer"),
     *                  @OA\Property(property="email", type="string", example="someone@somewhere.com"),
     *                  @OA\Property(property="password", type="string", example="str0ng12P4ssword?"),
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
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
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

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'provider' => isset($input['provider']) ? $input['provider'] : '',
                'registry_id' => isset($input['registry_id']) ? $input['registry_id'] : null,
                'keycloak_id' => null,
                'user_group' => Keycloak::determineUserGroup($input),
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $user->id,
            ], 201);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/users/{id}",
     *      summary="Edit a User entry",
     *      description="Edit a User entry",
     *      tags={"User"},
     *      summary="User@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="User ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="User definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="An Issuer"),
     *              @OA\Property(property="email", type="string", example="someone@somewhere.com"),
     *              @OA\Property(property="password", type="string", example="str0ng12P4ssword?"),
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
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
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
            
            $user = User::where('id', $id)->first();
            $user->name = isset($input['name']) ? $input['name'] : $user->name;
            $user->email = isset($input['email']) ? $input['email'] : $user->email;
            $user->password = isset($input['password']) ? Hash::make($input['password']) : $user->password;
            $user->registry_id = isset($input['registry_id']) ? $input['registry_id'] : $user->registry_id;

            if ($user->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $user,
                ], 200);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/users/{id}",
     *      summary="Edit a User entry",
     *      description="Edit a User entry",
     *      tags={"User"},
     *      summary="User@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="User ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="User definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="An Issuer"),
     *              @OA\Property(property="email", type="string", example="someone@somewhere.com"),
     *              @OA\Property(property="password", type="string", example="str0ng12P4ssword?"),
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
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
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
            
            $user = User::where('id', $id)->first();
            $user->name = isset($input['name']) ? $input['name'] : $user->name;
            $user->email = isset($input['email']) ? $input['email'] : $user->email;
            $user->password = isset($input['password']) ? Hash::make($input['password']) : $user->password;
            $user->registry_id = isset($input['registry_id']) ? $input['registry_id'] : $user->registry_id;

            if ($user->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $user,
                ], 200);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/users/{id}",
     *      summary="Delete a User entry from the system by ID",
     *      description="Delete a User entry from the system",
     *      tags={"User"},
     *      summary="User@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="User entry ID",
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
            User::where('id', $id)->delete();
            UserHasIssuerPermission::where('user_id', $id)->delete();
            UserHasIssuerApproval::where('user_id', $id)->delete();

            return response()->json([
                'message' => 'succes',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
