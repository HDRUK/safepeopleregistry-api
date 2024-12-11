<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use Hash;
use App\Http\Controllers\Controller;
use App\Models\Custodian;
use App\Models\Organisation;
use App\Models\Project;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustodianController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/custodians",
     *      summary="Return a list of Custodians",
     *      description="Return a list of Custodians",
     *      tags={"Custodian"},
     *      summary="Custodian@index",
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
     *                  @OA\Property(property="name", type="string", example="A Custodian"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="idvt_required", type="boolean", example="true")
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
        $custodians = Custodian::searchViaRequest()
            ->applySorting()
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json([
            'message' => 'success',
            'data' => $custodians,
        ], 200);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/custodians/{id}",
     *      summary="Return an Custodian entry by ID",
     *      description="Return an Custodian entry by ID",
     *      tags={"Custodian"},
     *      summary="Custodian@show",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Custodian ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Custodian ID",
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
     *                  @OA\Property(property="name", type="string", example="A Custodian"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="idvt_required", type="boolean", example="true")
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
        $custodian = Custodian::findOrFail($id);
        if ($custodian) {
            return response()->json([
                'message' => 'success',
                'data' => $custodian,
            ], 200);
        }

        return response()->json([
            'message' => 'not found',
            'data' => null,
        ], 404);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/custodians/identifier/{id}",
     *      summary="Return a Custodian entry by Unique Identifier",
     *      description="Return an Custodian entry by Unique Identifier",
     *      tags={"Custodian"},
     *      summary="Custodian@showByUniqueIdentifier",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Custodian Unique Identifier",
     *         required=true,
     *         example="c3eddb33-db74-4ea7-961a-778740f17e25",
     *
     *         @OA\Schema(
     *            type="string",
     *            description="Custodian Unique Identifier",
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
     *                  @OA\Property(property="name", type="string", example="A Custodian"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="idvt_required", type="boolean", example="true")
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
    public function showByUniqueIdentifier(Request $request, string $id): JsonResponse
    {
        $custodian = Custodian::where('unique_identifier', $id)->first();
        if ($custodian) {
            return response()->json([
                'message' => 'success',
                'data' => $custodian,
            ], 200);
        }

        return response()->json([
            'message' => 'not found',
            'data' => null,
        ], 404);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/custodians",
     *      summary="Create a Custodian entry",
     *      description="Create a Custodian entry",
     *      tags={"Custodian"},
     *      summary="Custodian@store",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Custodian definition",
     *
     *          @OA\JsonContent(
     *
     *                  @OA\Property(property="name", type="string", example="A Custodian"),
     *                  @OA\Property(property="enabled", type="boolean", example="true")
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
     *                  @OA\Property(property="name", type="string", example="A Custodian"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="idvt_required", type="boolean", example="true")
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
    public function store(Request $request): JsonResponse
    {
        try {
            $input = $request->all();

            $signature = Str::random(40);
            $calculatedHash = Hash::make(
                $signature.
                 ':'.env('CUSTODIAN_SALT_1').
                 ':'.env('CUSTODIAN_SALT_2')
            );

            $custodian = Custodian::create([
                'name' => $input['name'],
                'unique_identifier' => $signature,
                'calculated_hash' => $calculatedHash,
                'contact_email' => $input['contact_email'],
                'enabled' => $input['enabled'],
                'idvt_required' => (isset($input['idvt_required']) ? $input['idvt_required'] : false),
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $custodian->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/custodians/{id}",
     *      summary="Edit a Custodian entry",
     *      description="Edit a Custodian entry",
     *      tags={"Custodian"},
     *      summary="Custodian@update",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Custodian ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Custodian ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Custodian definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="name", type="string", example="A Custodian"),
     *              @OA\Property(property="enabled", type="boolean", example="true")
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
     *                  @OA\Property(property="name", type="string", example="A Custodian"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
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

            $custodian = Custodian::where('id', $id)->first();
            $custodian->name = $input['name'];
            $custodian->contact_email = isset($input['contact_email']) ? $input['contact_email'] : $custodian->contact_email;
            $custodian->enabled = $input['enabled'];
            $custodian->idvt_required = isset($input['idvt_required']) ? $input['idvt_required'] : false;

            if ($custodian->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $custodian,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => null,
                'error' => 'unable to save custodian',
            ], 400);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/custodians/{id}",
     *      summary="Edit a Custodian entry",
     *      description="Edit a Custodian entry",
     *      tags={"Custodian"},
     *      summary="Custodian@edit",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Custodian ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Custodian ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Custodian definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="name", type="string", example="A Custodian"),
     *              @OA\Property(property="enabled", type="boolean", example="true")
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
     *                  @OA\Property(property="name", type="string", example="A Custodian"),
     *                  @OA\Property(property="contact_email", type="string", example="person@somewhere.com"),
     *                  @OA\Property(property="enabled", type="boolean", example="true"),
     *                  @OA\Property(property="invite_accepted_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="invite_sent_at", type="string", example="2024-02-04 12:00:00"),
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

            $custodian = Custodian::where('id', $id)->first();
            $custodian->name = $input['name'];
            $custodian->contact_email = isset($input['contact_email']) ? $input['contact_email'] : $custodian->contact_email;
            $custodian->enabled = $input['enabled'];
            $custodian->idvt_required = isset($input['idvt_required']) ? $input['idvt_required'] : $custodian->idvt_required;

            if ($custodian->save()) {
                return response()->json([
                    'message' => 'success',
                    'data' => $custodian,
                ], 200);
            }

            return response()->json([
                'message' => 'failed',
                'data' => null,
                'error' => 'unable to save custodian',
            ], 400);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/custodians/{id}",
     *      summary="Delete a Custodian entry from the system by ID",
     *      description="Delete a Custodian entry from the system",
     *      tags={"Custodian"},
     *      summary="Custodian@destroy",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Custodian entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Custodian entry ID",
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
            Custodian::where('id', $id)->delete();

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
            $projectsAddedCount = 0;
            $organisationsAddedCount = 0;
            $researchersAddedCount = 0;

            // Traverse incoming payload and create entities pushed to us
            $custodianId = $request->header('x-custodian-key');
            $input = $request->all();

            if (! $custodianId) {
                return response()->json([
                    'message' => 'you must be a trusted custodian and provide your custodian-key within the request headers',
                ], 401);
            }

            foreach ($input['projects'] as $p) {
                $project = Project::firstOrCreate(
                    ['unique_id' => $p['unique_id']],
                    [
                        'title' => $p['title'],
                        'lay_summary' => $p['lay_summary'],
                        'public_benefit' => $p['public_benefit'],
                        'request_category_type' => $p['request_category_type'],
                        'technical_summary' => $p['technical_summary'],
                        'other_approval_committees' => $p['other_approval_committees'],
                        'start_date' => $p['start_date'],
                        'end_date' => $p['end_date'],
                        'affiliate_id' => $p['affiliate_id'],
                    ]
                );

                if ($project) {
                    $projectsAddedCount++;
                }
            }

            foreach ($input['organisations'] as $org) {
                $organisation = Organisation::firstOrCreate(
                    ['organisation_unique_id' => $org['organisation_unique_id']],
                    [
                        'organisation_name' => $org['organisation_name'],
                        'address_1' => $org['address_1'],
                        'address_2' => $org['address_2'],
                        'town' => $org['town'],
                        'county' => $org['county'],
                        'country' => $org['country'],
                        'postcode' => $org['postcode'],
                        'lead_applicant_organisation_name' => $org['lead_applicant_organisation_name'],
                        'organisation_unique_id' => $org['organisation_unique_id'],
                        'applicant_names' => $org['applicant_names'],
                        'funders_and_sponsors' => $org['funders_and_sponsors'],
                        'sub_license_arrangements' => $org['sub_license_arrangements'],
                        'companies_house_no' => $org['companies_house_no'],
                        'sector_id' => $org['sector_id'],
                    ]
                );

                if ($organisation) {
                    $organisationsAddedCount++;
                }
            }

            foreach ($input['researchers'] as $researcher) {
                // TBC
                if ($researcher) {
                    $researchersAddedCount++;
                }
            }

            return response()->json([
                'message' => 'success',
                'data' => [
                    'projects_created' => $projectsAddedCount,
                    'organisations_created' => $organisationsAddedCount,
                    'researchers_created' => $researchersAddedCount,

                ],
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
        * @OA\Get(
        *      path="/api/v1/custodian/{id}/projects",
        *      summary="Return an all projects associated with a Custodian",
        *      description="Return an all projects associated with a Custodian (i.e. data-custodian)",
        *      tags={"Custodian"},
        *      summary="Custodian@getPorjects",
        *      security={{"bearerAuth":{}}},
        *
        *      @OA\Parameter(
        *         name="id",
        *         in="path",
        *         description="Custodian ID",
        *         required=true,
        *         example="1",
        *
        *         @OA\Schema(
        *            type="integer",
        *            description="Custodian ID",
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
        *                  @OA\Property(property="registry_id", type="integer", example="1"),
        *                  @OA\Property(property="name", type="string", example="My First Research Project"),
        *                  @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
        *                  @OA\Property(property="runs_to", type="string", example="2026-02-04"),
        *                  @OA\Property(property="affiliate_id", type="integer", example="2"),
        *              ),
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
    public function getProjects(Request $request, int $custodianId): JsonResponse
    {
        $projects = Project::with('approvals')
                    ->where('affiliate_id', $custodianId)
                    ->paginate((int)$this->getSystemConfig('PER_PAGE'));
        if ($projects) {
            return response()->json([
                'message' => 'success',
                'data' => $projects,
            ], 200);
        }

        return response()->json([
            'message' => 'not found',
            'data' => null,
        ], 404);
    }


}
