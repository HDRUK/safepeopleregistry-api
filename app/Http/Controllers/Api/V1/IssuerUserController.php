<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\IssuerUser;
use App\Models\IssuerUserHasPermission;
use App\Models\Permission;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssuerUserController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/issuers_users",
     *      summary="Return a list of Issuer Users",
     *      description="Return a list of Issuer Users",
     *      tags={"IssuerUsers"},
     *      summary="IssuerUsers@index",
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
     *                  @OA\Property(property="first_name", type="string", example="David"),
     *                  @OA\Property(property="last_name", type="string", example="Davidson"),
     *                  @OA\Property(property="email", type="string", example="david@davidson.com"),
     *                  @OA\Property(property="issuer_id", type="integer", example="1")
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
        $users = IssuerUser::all();

        return response()->json([
            'message' => 'success',
            'data' => $users,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/issuers_users/{id}",
     *      summary="Return an IssuerUser entry by ID",
     *      description="Return an IssuerUser entry by ID",
     *      tags={"IssuerUser"},
     *      summary="IssuerUser@show",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="IssuerUser entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="IssuerUser entry ID",
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
     *                  @OA\Property(property="first_name", type="string", example="David"),
     *                  @OA\Property(property="last_name", type="string", example="Davidson"),
     *                  @OA\Property(property="email", type="string", example="david@davidson.com"),
     *                  @OA\Property(property="issuer_id", type="integer", example="1")
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
        $user = IssuerUser::where('id', $id)->first();

        return response()->json([
            'message' => 'success',
            'data' => $user,
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/issuers_users",
     *      summary="Create an IssuerUser entry",
     *      description="Create an IssuerUser entry",
     *      tags={"IssuerUser"},
     *      summary="IssuerUser@store",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="IssuerUser definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="issuer_id", type="integer", example="1"),
     *              @OA\Property(property="first_name", type="string", example="First"),
     *              @OA\Property(property="last_name", type="string", example="Last"),
     *              @OA\Property(property="email", type="string", example="first@last.com"),
     *              @OA\Property(property="password", type="string", example="SomeP4ssw0rd!")
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
     *              @OA\Property(property="data", type="integer", example="1")
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

            $user = IssuerUser::create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'provider' => '',
                'keycloak_id' => '',
                'issuer_id' => $input['issuer_id'],
            ]);

            if (isset($input['permissions'])) {
                IssuerUserHasPermission::where([
                    'issuer_user_id' => $user->id,
                ])->delete();

                $perms = Permission::whereIn('id', $input['permissions'])->get();
                foreach ($perms as $perm) {
                    $p = IssuerUserHasPermission::create([
                        'issuer_user_id' => $user->id,
                        'permission_id' => $perm->id,
                    ]);
                }
            }

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
     *      path="/api/v1/issuers_users",
     *      summary="Update an IssuerUser entry",
     *      description="Update an IssuerUser entry",
     *      tags={"IssuerUser"},
     *      summary="IssuerUser@update",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="IssuerUser definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="issuer_id", type="integer", example="1"),
     *              @OA\Property(property="first_name", type="string", example="First"),
     *              @OA\Property(property="last_name", type="string", example="Last"),
     *              @OA\Property(property="email", type="string", example="first@last.com"),
     *              @OA\Property(property="password", type="string", example="SomeP4ssw0rd!")
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
     *                  @OA\Property(property="first_name", type="string", example="David"),
     *                  @OA\Property(property="last_name", type="string", example="Davidson"),
     *                  @OA\Property(property="email", type="string", example="david@davidson.com"),
     *                  @OA\Property(property="issuer_id", type="integer", example="1")
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
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            $user = IssuerUser::where('id', $id)->first();
            $user->first_name = isset($input['first_name']) ? $input['first_name'] : $user->first_name;
            $user->last_name = isset($input['last_name']) ? $input['last_name'] : $user->last_name;
            $user->email = isset($input['email']) ? $input['email'] : $user->email;
            $user->password = isset($input['password']) ? Hash::make($input['password']) : $user->password;
            $user->provider = isset($input['provider']) ? $input['provider'] : $user->provider;
            $user->keycloak_id = isset($input['keycloak_id']) ? $input['keycloak_id'] : $user->keycloak_id;
            $user->issuer_id = isset($input['issuer_id']) ? $input['issuer_id'] : $user->issuer_id;

            if (isset($input['permissions'])) {
                IssuerUserHasPermission::where([
                    'issuer_user_id' => $user->id,
                ])->delete();

                $perms = Permission::whereIn('id', $input['permissions'])->get();
                foreach ($perms as $perm) {
                    $p = IssuerUserHasPermission::create([
                        'issuer_user_id' => $user->id,
                        'permission_id' => $perm->id,
                    ]);
                }
            }

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
     *      path="/api/v1/issuers_users",
     *      summary="Edit an IssuerUser entry",
     *      description="Edit an IssuerUser entry",
     *      tags={"IssuerUser"},
     *      summary="IssuerUser@edit",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          description="IssuerUser definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="issuer_id", type="integer", example="1"),
     *              @OA\Property(property="first_name", type="string", example="First"),
     *              @OA\Property(property="last_name", type="string", example="Last"),
     *              @OA\Property(property="email", type="string", example="first@last.com"),
     *              @OA\Property(property="password", type="string", example="SomeP4ssw0rd!")
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
     *                  @OA\Property(property="first_name", type="string", example="David"),
     *                  @OA\Property(property="last_name", type="string", example="Davidson"),
     *                  @OA\Property(property="email", type="string", example="david@davidson.com"),
     *                  @OA\Property(property="issuer_id", type="integer", example="1")
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
    public function edit(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            $user = IssuerUser::where('id', $id)->first();
            $user->first_name = isset($input['first_name']) ? $input['first_name'] : $user->first_name;
            $user->last_name = isset($input['last_name']) ? $input['last_name'] : $user->last_name;
            $user->email = isset($input['email']) ? $input['email'] : $user->email;
            $user->password = isset($input['password']) ? Hash::make($input['password']) : $user->password;
            $user->provider = isset($input['provider']) ? $input['provider'] : $user->provider;
            $user->keycloak_id = isset($input['keycloak_id']) ? $input['keycloak_id'] : $user->keycloak_id;
            $user->issuer_id = isset($input['issuer_id']) ? $input['issuer_id'] : $user->issuer_id;

            if (isset($input['permissions'])) {
                IssuerUserHasPermission::where([
                    'issuer_user_id' => $user->id,
                ])->delete();

                $perms = Permission::whereIn('id', $input['permissions'])->get();
                foreach ($perms as $perm) {
                    $p = IssuerUserHasPermission::create([
                        'issuer_user_id' => $user->id,
                        'permission_id' => $perm->id,
                    ]);
                }
            }

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
     *      path="/api/v1/issuers_users/{id}",
     *      summary="Delete an IssuerUser entry from the system by ID",
     *      description="Delete a IssuerUser entry from the system",
     *      tags={"IssuerUser"},
     *      summary="IssuerUser@destroy",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="IssuerUser entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="IssuerUser entry ID",
     *         ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="not found")
     *           ),
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="success")
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
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $user = IssuerUser::where('id', $id)->first();
            IssuerUserHasPermission::where('issuer_user_id', $user->id)->delete();

            $user->delete();

            return response()->json([
                'message' => 'success',
                'data' => null,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
