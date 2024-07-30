<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Exception;

use App\Models\User;
use App\Models\Registry;
use App\Exceptions\NotFoundException;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QueryController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/v1/query",
     *      summary="Query the registry by Digital Identifier",
     *      description="Query the registry by Digital Identifier",
     *      tags={"Query"},
     *      summary="Query@query",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Query definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="ident", type="string", example="$2y$12$V6SSFQLyQDQRZxvz.Tswa.HA.ixJIXofs7.omitted")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *                  @OA\Property(property="message", type="string", example="success"),
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer", example="1"),
     *                          @OA\Property(property="created_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                          @OA\Property(property="updated_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                          @OA\Property(property="deleted_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                          @OA\Property(property="verified", type="boolean", example="true"),
     *                          @OA\Property(property="user", type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="user_id", type="integer", example="1"),
     *                                  @OA\Property(property="name", type="string", example="Some One"),
     *                                  @OA\Property(property="email", type="string", example="someone@somewhere.com"),
     *                                  @OA\Property(property="email_verified_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                                  @OA\Property(property="created_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="updated_at", type="string", example="2024-03-12T13:11:55.000000Z")
     *                              )
     *                          ),
     *                          @OA\Property(property="identity", type="array", 
     *                              @OA\Items(
     *                                  @OA\Property(property="id", type="integer", example="1"),
     *                                  @OA\Property(property="created_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="updated_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="deleted_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="registry_id", type="integer", example="1")
     *                              )
     *                          ),
     *                          @OA\Property(property="history", type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="id", type="integer", example="1"),
     *                                  @OA\Property(property="created_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="updated_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="employment_id", type="integer", example="1"),
     *                                  @OA\Property(property="endorsement_id", type="integer", example="234"),
     *                                  @OA\Property(property="infringement_id", type="integer", example="456"),
     *                                  @OA\Property(property="project_id", type="integer", example="1"),
     *                                  @OA\Property(property="access_key_id", type="integer", example="876"),
     *                                  @OA\Property(property="issuer_identifier", type="string", example="ABC1234DEF-56789-0")
     *                              )
     *                          ),
     *                          @OA\Property(property="training", type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="id", type="integer", example="1"),
     *                                  @OA\Property(property="created_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="updated_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                                  @OA\Property(property="provider", type="string", example="Training Provider Name"),
     *                                  @OA\Property(property="awarded_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="expires_at", type="string", example="2029-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="expires_in_years", type="integer", example="5"),
     *                                  @OA\Property(property="training_name", type="string", example="Training Course Name")
     *                              )
     *                          ),
     *                          @OA\Property(property="projects", type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="id", type="integer", example="1"),
     *                                  @OA\Property(property="created_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="updated_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="deleted_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                                  @OA\Property(property="name", type="string", example="Project Name"),
     *                                  @OA\Property(property="public_benefit", type="string", example="Public Benefit statement"),
     *                                  @OA\Property(property="runs_to", type="string", example="2024-09-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="affiliate_id", type="integer", example="124")
     *                              )
     *                          ),
     *                          @OA\Property(property="organisations", type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="id", type="integer", example="1"),
     *                                  @OA\Property(property="created_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="updated_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                                  @OA\Property(property="name", type="string", example="Institute Name")
     *                              )
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      )
     *  )
     */
    public function query(Request $request): JsonResponse
    {
        $input = $request->all();

        $registry = User::with([
            'registry',
            'registry.identity',
            'registry.history',
            'registry.training',
            'registry.organisations',
            'history.endorsements',
            'history.infringements',
        ])->where('digi_ident', $input['ident'])->first();

        // $registry = DB::table('users')
        //     ->join('registries', 'users.registry_id', 'registries.id')
        //     ->join('identities', 'identities.registry_id', 'registries.id')
        //     ->join('histories', 'registry_id', 'registries.id')
        //     ->join('trainings', 'trainings.registry_id', 'registries.id')
        //     ->join('organisations', 'organisations.registry_id', 'registries.id')
        //     ->where('registries.digi_ident', $input['ident'])
        //     ->get();

        // $registry = User::with([
        //     'registry',
        //     'registry.identity',
        //     'registry.history',
        //     'registry.training',
        //     'registry.organisations',
        // ])->where('digi_ident', $input['ident'])->first();
        if ($registry) {
            return response()->json([
                'message' => 'success',
                'data' => $registry,
            ], 200);
        }

        throw new NotFoundException();
    }
}
