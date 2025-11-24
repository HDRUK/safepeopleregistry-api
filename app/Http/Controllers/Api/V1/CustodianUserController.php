<?php

namespace App\Http\Controllers\Api\V1;

use Hash;
use Keycloak;
use Exception;
use TriggerEmail;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\CustodianUser;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use RegistryManagementController as RMC;
use App\Models\CustodianUserHasPermission;
use App\Http\Requests\CustodianUsers\GetCustodianUser;
use App\Http\Requests\CustodianUsers\DeleteCustodianUser;
use App\Http\Requests\CustodianUsers\InviteCustodianUser;
use App\Http\Requests\CustodianUsers\UpdateCustodianUser;

class CustodianUserController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/custodian_users",
     *      summary="Return a list of Custodian Users",
     *      description="Return a list of Custodian Users",
     *      tags={"CustodianUsers"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/CustodianUser"
     *              ),
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
        $users = CustodianUser::with("userPermissions.permission")->get();

        return response()->json([
            'message' => 'success',
            'data' => $users,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/custodian_users/{id}",
     *      summary="Return a CustodianUser entry by ID",
     *      description="Return a CustodianUser entry by ID",
     *      tags={"CustodianUser"},
     *      summary="CustodianUser@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="CustodianUser entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="CustodianUser entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  ref="#/components/schemas/CustodianUser"
     *              ),
     *              @OA\Property(property="user_permissions", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="custodian_user_id", type="integer", example=1),
     *                      @OA\Property(property="permission_id", type="integer", example=10),
     *                      @OA\Property(
     *                          property="permission",
     *                          type="object",
     *                          ref="#/components/schemas/Permission"
     *                      )
     *                  )
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)"),
     *          )
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
    public function show(GetCustodianUser $request, int $id): JsonResponse
    {
        $user = CustodianUser::where('id', $id)->first();

        if ($user) {
            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 200);
        }

        return response()->json([
            'message' => 'not found',
            'data' => null,
        ], 404);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/custodian_users",
     *      summary="Create a CustodianUser entry",
     *      description="Create a CustodianUser entry",
     *      tags={"CustodianUser"},
     *      summary="CustodianUser@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="CustodianUser definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/CustodianUser"
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
     *              @OA\Property(property="data", type="integer", example="1")
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

            $user = CustodianUser::create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'provider' => '',
                'keycloak_id' => '',
                'custodian_id' => $input['custodian_id'],
            ]);

            if (isset($input['permissions'])) {
                CustodianUserHasPermission::where([
                    'custodian_user_id' => $user->id,
                ])->delete();

                $perms = Permission::whereIn('id', $input['permissions'])->get();

                foreach ($perms as $perm) {
                    $p = CustodianUserHasPermission::create([
                        'custodian_user_id' => $user->id,
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
     *      path="/api/v1/custodian_users",
     *      summary="Update a CustodianUser entry",
     *      description="Update a CustodianUser entry",
     *      tags={"CustodianUser"},
     *      summary="CustodianUser@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="CustodianUser definition",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/CustodianUser"
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
     *                  ref="#/components/schemas/CustodianUser"
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          )
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
    public function update(UpdateCustodianUser $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            $user = CustodianUser::where('id', $id)->first();
            $user->first_name = isset($input['first_name']) ? $input['first_name'] : $user->first_name;
            $user->last_name = isset($input['last_name']) ? $input['last_name'] : $user->last_name;
            $user->email = isset($input['email']) ? $input['email'] : $user->email;
            $user->password = isset($input['password']) ? Hash::make($input['password']) : $user->password;
            $user->provider = isset($input['provider']) ? $input['provider'] : $user->provider;
            $user->keycloak_id = isset($input['keycloak_id']) ? $input['keycloak_id'] : $user->keycloak_id;
            $user->custodian_id = isset($input['custodian_id']) ? $input['custodian_id'] : $user->custodian_id;

            if (isset($input['permissions'])) {
                CustodianUserHasPermission::where([
                    'custodian_user_id' => $user->id,
                ])->delete();

                $perms = Permission::whereIn('id', $input['permissions'])->get();
                foreach ($perms as $perm) {
                    $p = CustodianUserHasPermission::create([
                        'custodian_user_id' => $user->id,
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

            return response()->json([
                'message' => 'failed',
                'data' => null,
                'error' => 'unable to save custodian user',
            ], 400);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger docs
    public function invite(InviteCustodianUser $request, int $id): JsonResponse
    {
        try {
            $user = CustodianUser::where('id', $id)->first();

            $custodianUserPermissions = CustodianUserHasPermission::where('custodian_user_id', $id)->first();
            $permissions = Permission::where('id', $custodianUserPermissions->permission_id)->first();

            /**
             * Observer is also creating the keycloak user
             */
            $unclaimedUser = RMC::createUnclaimedUser([
                'firstname' => $user['first_name'],
                'lastname' => $user['last_name'],
                'email' => $user['email'],
                'user_group' => 'CUSTODIANS',
                'custodian_user_id' => $id,
                'invited_by_custodian' => true,
            ]);

            $emailIdentifier = '';
            if ($permissions->name === 'CUSTODIAN_ADMIN') {
                $emailIdentifier = 'custodian_invite_administrator';
            }

            if ($permissions->name === 'CUSTODIAN_APPROVER') {
                $emailIdentifier = 'custodian_invite_approver';
            }

            $input = [
                'type' => 'CUSTODIAN_USER',
                'to' => $user->id,
                'unclaimed_user_id' => $unclaimedUser->id,
                'by' => $id,
                'identifier' => $emailIdentifier
            ];

            TriggerEmail::spawnEmail($input);

            return response()->json([
                'message' => 'success',
                'data' => $user,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/custodian_users/{id}",
     *      summary="Delete a CustodianUser entry from the system by ID",
     *      description="Delete a CustodianUser entry from the system",
     *      tags={"CustodianUser"},
     *      summary="CustodianUser@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="CustodianUser entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="CustodianUser entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *           ),
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
    public function destroy(DeleteCustodianUser $request, int $id): JsonResponse
    {
        try {
            $user = CustodianUser::where('id', $id)->first();
            CustodianUserHasPermission::where('custodian_user_id', $user->id)->delete();

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
