<?php

namespace App\Http\Controllers\Api\V1;

use Hash;
use Keycloak;
use Exception;
use App\Models\User;
use App\Models\UserHasCustodianApproval;
use App\Models\UserHasCustodianPermission;
use App\Http\Requests\Users\CreateUser;
use App\Exceptions\NotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Traits\CommonFunctions;
use App\Traits\CheckPermissions;

class UserController extends Controller
{
    use CommonFunctions;
    use CheckPermissions;

    /**
     * @OA\Get(
     *      path="/api/v1/users",
     *      summary="Return a list of Users",
     *      description="Return a list of Users",
     *      tags={"User"},
     *      summary="User@index",
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
     *                  @OA\Property(property="first_name", type="string", example="A"),
     *                  @OA\Property(property="last_name", type="string", example="Researcher"),
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="consent_scrape", type="boolean", example="true"),
     *                  @OA\Property(property="public_opt_in", type="boolean", example="true"),
     *                  @OA\Property(property="declaration_signed", type="boolean", example="true"),
     *                  @OA\Property(property="organisation_id", type="integer", example="123"),
     *                  @OA\Property(property="orcid_scanning", type="integer", example="1"),
     *                  @OA\Property(property="orcid_scanning_completed_at", type="string", example="2024-02-04 12:01:00")
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
        $users = User::searchViaRequest()
            ->with([
            'permissions',
            'registry',
            'registry.files',
            'pendingInvites',
            'organisation',
            'departments',
        ])->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json(
            [
                'message' => 'success',
                'data' => $users,
            ],
            200
        );
    }

    /**
     * @OA\Get(
     *      path="/api/v1/users/{id}",
     *      summary="Return a User entry by ID",
     *      description="Return a User entry by ID",
     *      tags={"User"},
     *      summary="User@show",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="User ID",
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
     *                  @OA\Property(property="first_name", type="string", example="A"),
     *                  @OA\Property(property="last_name", type="string", example="Researcher"),
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="consent_scrape", type="boolean", example="true"),
     *                  @OA\Property(property="profile_steps_completed", type="string", example="{}"),
     *                  @OA\Property(property="profile_completed_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="public_opt_in", type="boolean", example="true"),
     *                  @OA\Property(property="declaration_signed", type="boolean", example="true"),
     *                  @OA\Property(property="organisation_id", type="integer", example="123"),
     *                  @OA\Property(property="orcid_scanning", type="integer", example="1"),
     *                  @OA\Property(property="orcid_scanning_completed_at", type="string", example="2024-02-04 12:01:00")
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
        try {
            $user = User::with([
                'permissions',
                'registry',
                'registry.files',
                'pendingInvites',
                'organisation',
                'departments',
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
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="User definition",
     *
     *          @OA\JsonContent(
     *
     *                  @OA\Property(property="first_name", type="string", example="A"),
     *                  @OA\Property(property="last_name", type="string", example="Researcher"),
     *                  @OA\Property(property="email", type="string", example="someone@somewhere.com"),
     *                  @OA\Property(property="password", type="string", example="str0ng12P4ssword?"),
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
     *                  @OA\Property(property="first_name", type="string", example="A"),
     *                  @OA\Property(property="last_name", type="string", example="Researcher"),
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="consent_scrape", type="boolean", example="true"),
     *                  @OA\Property(property="profile_steps_completed", type="string", example="{}"),
     *                  @OA\Property(property="profile_completed_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="public_opt_in", type="boolean", example="true"),
     *                  @OA\Property(property="declaration_signed", type="boolean", example="true"),
     *                  @OA\Property(property="organisation_id", type="integer", example="123"),
     *                  @OA\Property(property="orcid_scanning", type="integer", example="1"),
     *                  @OA\Property(property="orcid_scanning_completed_at", type="string", example="2024-02-04 12:01:00")
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
    public function store(CreateUser $request): JsonResponse
    {
        try {
            $input = $request->all();

            $user = User::create([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'provider' => isset($input['provider']) ? $input['provider'] : '',
                'registry_id' => isset($input['registry_id']) ? $input['registry_id'] : null,
                'keycloak_id' => null,
                'user_group' => Keycloak::determineUserGroup($input),
                'consent_scrape' => isset($input['consent_scrape']) ? $input['consent_scrape'] : 0,
                'profile_steps_completed' => isset($input['profile_steps_completed']) ? $input['profile_steps_completed'] : null,
                'profile_completed_at' => isset($input['profile_completed_at']) ? $input['profile_completed_at'] : null,
                'public_opt_in' => isset($input['public_opt_in']) ? $input['public_opt_in'] : false,
                'declaration_signed' => isset($input['declaration_signed']) ? $input['declaration_signed'] : false,
                'organisation_id' => isset($input['organisation_id']) ? $input['organisation_id'] : null,
            ]);

            // TODO - Close Pending invite when we're sure how org id is handled

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
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="User ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="User definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="first_name", type="string", example="A"),
     *              @OA\Property(property="last_name", type="string", example="Researcher"),
     *              @OA\Property(property="email", type="string", example="someone@somewhere.com"),
     *              @OA\Property(property="password", type="string", example="str0ng12P4ssword?"),
     *              @OA\Property(property="orc_id", type="string", example="0000-0000-0000-0000"),
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
     *          response=200,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="first_name", type="string", example="A"),
     *                  @OA\Property(property="last_name", type="string", example="Researcher"),
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="consent_scrape", type="boolean", example="true"),
     *                  @OA\Property(property="profile_steps_completed", type="string", example="{}"),
     *                  @OA\Property(property="profile_completed_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="public_opt_in", type="boolean", example="true"),
     *                  @OA\Property(property="declaration_signed", type="boolean", example="true"),
     *                  @OA\Property(property="organisation_id", type="integer", example="123"),
     *                  @OA\Property(property="orc_id", type="string", example="0000-0000-0000-0000"),
     *                  @OA\Property(property="orcid_scanning", type="integer", example="1"),
     *                  @OA\Property(property="orcid_scanning_completed_at", type="string", example="2024-02-04 12:01:00")
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

            $user = User::where('id', $id)->first();
            $user->first_name = isset($input['first_name']) ? $input['first_name'] : $user->first_name;
            $user->last_name = isset($input['last_name']) ? $input['last_name'] : $user->last_name;
            $user->email = isset($input['email']) ? $input['email'] : $user->email;
            $user->password = isset($input['password']) ? Hash::make($input['password']) : $user->password;
            $user->registry_id = isset($input['registry_id']) ? $input['registry_id'] : $user->registry_id;
            $user->consent_scrape = isset($input['consent_scrape']) ? $input['consent_scrape'] : $user->consent_scrape;
            $user->profile_steps_completed = isset($input['profile_steps_completed']) ? $input['profile_steps_completed'] : $user->profile_steps_completed;
            $user->profile_completed_at = isset($input['profile_completed_at']) ? $input['profile_completed_at'] : $user->profile_completed_at;
            $user->public_opt_in = isset($input['public_opt_in']) ? $input['public_opt_in'] : $user->public_opt_in;
            $user->declaration_signed = isset($input['declaration_signed']) ? $input['declaration_signed'] : $user->declaration_signed;
            $user->organisation_id = isset($input['organisation_id']) ? $input['organisation_id'] : $user->organisation_id;
            $user->orc_id = isset($input['orc_id']) ? $input['orc_id'] : $user->orc_id;

            if ($user->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $user,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => null,
                'error' => 'unable to save user',
            ], 409);
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
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="User ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="User definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="first_name", type="string", example="A"),
     *              @OA\Property(property="last_name", type="string", example="Researcher"),
     *              @OA\Property(property="email", type="string", example="someone@somewhere.com"),
     *              @OA\Property(property="password", type="string", example="str0ng12P4ssword?"),
     *              @OA\Property(property="orc_id", type="string", example="0000-0000-0000-0000"),
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
     *          response=200,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="first_name", type="string", example="A"),
     *                  @OA\Property(property="last_name", type="string", example="Researcher"),
     *                  @OA\Property(property="email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="email_verified_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="consent_scrape", type="boolean", example="true"),
     *                  @OA\Property(property="profile_steps_completed", type="string", example="{}"),
     *                  @OA\Property(property="profile_completed_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="public_opt_in", type="boolean", example="true"),
     *                  @OA\Property(property="declaration_signed", type="boolean", example="true"),
     *                  @OA\Property(property="organisation_id", type="integer", example="123"),
     *                  @OA\Property(property="orc_id", type="string", example="0000-0000-0000-0000"),
     *                  @OA\Property(property="orcid_scanning", type="integer", example="1"),
     *                  @OA\Property(property="orcid_scanning_completed_at", type="string", example="2024-02-04 12:01:00")
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

            $user = User::where('id', $id)->first();
            $user->first_name = isset($input['first_name']) ? $input['first_name'] : $user->first_name;
            $user->last_name = isset($input['last_name']) ? $input['last_name'] : $user->last_name;
            $user->email = isset($input['email']) ? $input['email'] : $user->email;
            $user->password = isset($input['password']) ? Hash::make($input['password']) : $user->password;
            $user->registry_id = isset($input['registry_id']) ? $input['registry_id'] : $user->registry_id;
            $user->consent_scrape = isset($input['consent_scrape']) ? $input['consent_scrape'] : $user->consent_scrape;
            $user->profile_steps_completed = isset($input['profile_steps_completed']) ? $input['profile_steps_completed'] : $user->profile_steps_completed;
            $user->profile_completed_at = isset($input['profile_completed_at']) ? $input['profile_completed_at'] : $user->profile_completed_at;
            $user->public_opt_in = isset($input['public_opt_in']) ? $input['public_opt_in'] : $user->public_opt_in;
            $user->declaration_signed = isset($input['declaration_signed']) ? $input['declaration_signed'] : $user->declaration_signed;
            $user->organisation_id = isset($input['organisation_id']) ? $input['organisation_id'] : $user->organisation_id;
            $user->orc_id = isset($input['orc_id']) ? $input['orc_id'] : $user->orc_id;

            if ($user->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $user,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => null,
                'error' => 'unable to save user',
            ], 400);
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
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="User entry ID",
     *         ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *           ),
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success")
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *
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
            UserHasCustodianPermission::where('user_id', $id)->delete();
            UserHasCustodianApproval::where('user_id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function fakeEndpointForTesting(Request $request): JsonResponse
    {
        $checkGroups = $this->hasGroups($request, ['admin']);
        if (!$checkGroups) {
            return response()->json([
                'message' => 'you do not have the required permissions to view this data',
                'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }

        $users = User::all();
        return response()->json([
            'message' => 'success',
            'data' => $users,
        ], Response::HTTP_OK);
    }
}
