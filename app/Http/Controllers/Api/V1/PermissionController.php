<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignOrganisationPermissionToFrom;
use App\Http\Requests\AssignUserPermissionToFrom;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\OrganisationHasCustodianPermission;
use App\Models\Permission;
use App\Models\User;
use App\Models\UserHasCustodianPermission;
use App\Traits\CommonFunctions;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/permissions",
     *      operationId="permissionIndex",
     *      x={"internal"="true"},
     *      summary="Return a list of Permissions",
     *      description="Return a list of Permissions",
     *      tags={"Permission"},
     *      summary="Permission@index",
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
     *                  @OA\Property(property="name", type="string", example="READ_SOMETHING"),
     *                  @OA\Property(property="enabled", type="boolean", example="true")
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
        $permissions = Permission::paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $permissions,
        ], 200);
    }

    /**
     * Hidden from Swagger documentation - no route is registered for this
     * method (routes/api.php only wires index, assignOrganisationPermissionsToFrom,
     * and assignUserPermissionsToFrom for this controller).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        if ($permission) {
            return response()->json([
                'message' => 'success',
                'data' => $permission,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * Hidden from Swagger documentation - no route is registered for this
     * method (routes/api.php only wires index, assignOrganisationPermissionsToFrom,
     * and assignUserPermissionsToFrom for this controller).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();

            $permission = Permission::create([
                'name' => $input['name'],
                'enabled' => $input['enabled'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $permission->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Hidden from Swagger documentation - no route is registered for this
     * method (routes/api.php only wires index, assignOrganisationPermissionsToFrom,
     * and assignUserPermissionsToFrom for this controller).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $input = $request->all();

            $perm = Permission::findOrFail($id);
            $perm->update([
                'name' => $input['name'],
                'enabled' => $input['enabled'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Permission::where('id', $id)->first(),
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Hidden from Swagger documentation - no route is registered for this
     * method (routes/api.php only wires index, assignOrganisationPermissionsToFrom,
     * and assignUserPermissionsToFrom for this controller).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            Permission::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function assignOrganisationPermissionsToFrom(AssignOrganisationPermissionToFrom $request): JsonResponse
    {
        try {
            $input = $request->all();

            $organisation = Organisation::where('id', $input['organisation_id'])->first();
            $custodian = Custodian::where('id', $input['custodian_id'])->first();
            $permissions = Permission::whereIn('id', $input['permissions'])->get();

            if (! $organisation || ! $custodian) {
                return response()->json([
                    'message' => 'missing organisation_id or custodian_id',
                    'data' => null,
                ], 400);
            }

            foreach ($permissions as $p) {
                OrganisationHasCustodianPermission::create([
                    'organisation_id' => $organisation->id,
                    'custodian_id' => $custodian->id,
                    'permission_id' => $p->id,
                ]);
            }

            return response()->json([
                'message' => 'success',
                'data' => true,
            ], 200);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function assignUserPermissionsToFrom(AssignUserPermissionToFrom $request): JsonResponse
    {
        try {
            $input = $request->all();

            $user = User::where('id', $input['user_id'])->first();
            $custodian = Custodian::where('id', $input['custodian_id'])->first();
            $permissions = Permission::whereIn('id', $input['permissions'])->get();

            if (! $user || ! $custodian) {
                return response()->json([
                    'message' => 'missing user_id or custodian_id',
                    'data' => null,
                ], 400);
            }

            foreach ($permissions as $p) {
                UserHasCustodianPermission::create([
                    'user_id' => $user->id,
                    'custodian_id' => $custodian->id,
                    'permission_id' => $p->id,
                ]);
            }

            return response()->json([
                'message' => 'success',
                'data' => true,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
