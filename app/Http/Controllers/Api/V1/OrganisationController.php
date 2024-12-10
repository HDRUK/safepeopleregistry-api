<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Hash;
use Exception;
use Carbon\Carbon;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Jobs\OrganisationIDVT;
use App\Models\Project;
use App\Models\Organisation;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/organisations",
     *      summary="Return a list of organisations",
     *      description="Return a list of organisations",
     *      tags={"organisation"},
     *      summary="organisation@index",
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
     *                  @OA\Property(property="name", type="string", example="Organisations Name"),
     *                  @OA\Property(property="address_1", type="string", example="123 Road"),
     *                  @OA\Property(property="address_2", type="string", example="Address Two"),
     *                  @OA\Property(property="town", type="string", example="Town"),
     *                  @OA\Property(property="county", type="string", example="County"),
     *                  @OA\Property(property="country", type="string", example="Country"),
     *                  @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *                  @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *                  @OA\Property(property="verified", type="boolean", example="true"),
     *                  @OA\Property(property="dsptk_ods_code", type="string", example="UY67FO"),
     *                  @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *                  @OA\Property(property="ce_certified", type="boolean", example="false"),
     *                  @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *                  @OA\Property(property="companies_house_no", type="string", example="12345678")
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
        $organisations = [];

        $issuerId = $request->get('issuer_id');
        if (! $issuerId) {
            $organisations = Organisation::searchViaRequest()
                ->applySorting()
                ->with([
                    'approvals',
                    'permissions',
                    'files',
                    'registries',
                    'registries.user',
                    'registries.user.permissions',
                    'registries.user.approvals',
                ])->paginate((int)$this->getSystemConfig('PER_PAGE'));
        }

        return response()->json(
            $organisations,
            200
        );
    }

    /**
     * @OA\Get(
     *      path="/api/v1/organisations/{id}",
     *      summary="Return an organisations entry by ID",
     *      description="Return an organisations entry by ID",
     *      tags={"organisations"},
     *      summary="organisations@show",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="organisations entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="organisations entry ID",
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
     *                  @OA\Property(property="name", type="string", example="Organisation Name"),
     *                  @OA\Property(property="address_1", type="string", example="123 Road"),
     *                  @OA\Property(property="address_2", type="string", example="Address Two"),
     *                  @OA\Property(property="town", type="string", example="Town"),
     *                  @OA\Property(property="county", type="string", example="County"),
     *                  @OA\Property(property="country", type="string", example="Country"),
     *                  @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *                  @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *                  @OA\Property(property="verified", type="boolean", example="true"),
     *                  @OA\Property(property="dsptk_ods_code", type="string", example="UY67FO"),
     *                  @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *                  @OA\Property(property="ce_certified", type="boolean", example="false"),
     *                  @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *                  @OA\Property(property="companies_house_no", type="string", example="12345678")
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
        $organisation = Organisation::with([
            'permissions',
            'approvals',
            'files',
            'registries',
            'registries.user',
            'registries.user.permissions',
            'registries.user.approvals',
        ])->findOrFail($id);
        if ($organisation) {
            return response()->json([
                'message' => 'success',
                'data' => $organisation,
            ], 200);
        }

        throw new NotFoundException();
    }

    // No swagger, internal call
    public function pastProjects(Request $request, int $id): JsonResponse
    {
        $projects = Project::with('organisations')
            ->where('start_date', '<', Carbon::now())
            ->where('end_date', '<', Carbon::now())
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json(
            $projects,
            200
        );
    }

    // No swagger, internal call
    public function presentProjects(Request $request, int $id): JsonResponse
    {
        $projects = Project::with('organisations')
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json(
            $projects,
            200
        );
    }

    // No swagger, internal call
    public function futureProjects(Request $request, int $id): JsonResponse
    {
        $projects = Project::with('organisations')
            ->where('start_date', '>', Carbon::now())
            ->where('end_date', '>', Carbon::now())
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json(
            $projects,
            200
        );
    }

    /**
     * @OA\Get(
     *      path="/api/v1/organisations/{id}/idvt",
     *      summary="Return an organisations idvt details by ID",
     *      description="Return an organisations idvt details by ID",
     *      tags={"organisations"},
     *      summary="organisations@idvt",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="organisations entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="organisations entry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="idvt_result", type="boolean", example="true"),
     *                  @OA\Property(property="idvt_result_perc", type="number", example="80"),
     *                  @OA\Property(property="idvt_errors", type="object", example="{}"),
     *                  @OA\Property(property="idvt_completed_at", type="string", example="2024-02-04 12:01:00")
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
    public function idvt(Request $request, int $id): JsonResponse
    {
        $organisation = Organisation::findOrFail($id);

        if ($organisation) {
            return response()->json([
                'message' => 'success',
                'data' => [
                    'id' => $organisation->id,
                    'idvt_result' => $organisation->idvt_result,
                    'idvt_errors' => $organisation->idvt_errors,
                    'idvt_completed_at' => $organisation->idvt_completed_at,
                    'idvt_result_perc' => $organisation->idvt_result_perc
                ],
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/organisations",
     *      summary="Create an organisations entry",
     *      description="Create a organisations entry",
     *      tags={"organisations"},
     *      summary="organisations@store",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="organisations definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="name", type="string", example="organisations Name"),
     *              @OA\Property(property="address_1", type="string", example="123 Road"),
     *              @OA\Property(property="address_2", type="string", example="Address Two"),
     *              @OA\Property(property="town", type="string", example="Town"),
     *              @OA\Property(property="county", type="string", example="County"),
     *              @OA\Property(property="country", type="string", example="Country"),
     *              @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *              @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *              @OA\Property(property="verified", type="boolean", example="true"),
     *              @OA\Property(property="dsptk_ods_code", type="string", example="UY67FO"),
     *              @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *              @OA\Property(property="ce_certified", type="boolean", example="false"),
     *              @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *              @OA\Property(property="companies_house_no", type="string", example="12345678")
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
            $organisation = Organisation::create([
                'organisation_name' => $input['organisation_name'],
                'address_1' => $input['address_1'],
                'address_2' => $input['address_2'],
                'town' => $input['town'],
                'county' => $input['county'],
                'country' => $input['country'],
                'postcode' => $input['postcode'],
                'lead_applicant_organisation_name' => $input['lead_applicant_organisation_name'],
                'lead_applicant_email' => $input['lead_applicant_email'],
                'password' => Hash::make($input['password']),
                'organisation_unique_id' => $input['organisation_unique_id'],
                'applicant_names' => $input['applicant_names'],
                'funders_and_sponsors' => $input['funders_and_sponsors'],
                'sub_license_arrangements' => $input['sub_license_arrangements'],
                'verified' => $input['verified'],
                'companies_house_no' => $input['companies_house_no'],
                'sector_id' => $input['sector_id'],
                'dsptk_certified' => $input['dsptk_certified'],
                'dsptk_certification_num' => $input['dsptk_certification_num'],
                'iso_27001_certified' => $input['iso_27001_certified'],
                'iso_27001_certification_num' => $input['iso_27001_certification_num'],
                'ce_certified' => $input['ce_certified'],
                'ce_certification_num' => $input['ce_certification_num'],
            ]);

            // Run automated IDVT
            if (env('APP_ENV') !== 'testing') {
                OrganisationIDVT::dispatchSync($organisation);
            }

            return response()->json([
                'message' => 'success',
                'data' => $organisation->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/organisations/{id}",
     *      summary="Update an organisations entry",
     *      description="Update a organisations entry",
     *      tags={"organisations"},
     *      summary="organisations@update",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="organisations entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="organisations entry ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="organisations definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="name", type="string", example="organisations Name"),
     *              @OA\Property(property="address_1", type="string", example="123 Road"),
     *              @OA\Property(property="address_2", type="string", example="Address Two"),
     *              @OA\Property(property="town", type="string", example="Town"),
     *              @OA\Property(property="county", type="string", example="County"),
     *              @OA\Property(property="country", type="string", example="Country"),
     *              @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *              @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *              @OA\Property(property="verified", type="boolean", example="true"),
     *              @OA\Property(property="dsptk_ods_code", type="string", example="UY67FO"),
     *              @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *              @OA\Property(property="ce_certified", type="boolean", example="false"),
     *              @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *              @OA\Property(property="companies_house_no", type="string", example="12345678")
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
     *                  @OA\Property(property="name", type="string", example="organisations Name"),
     *                  @OA\Property(property="address_1", type="string", example="123 Road"),
     *                  @OA\Property(property="address_2", type="string", example="Address Two"),
     *                  @OA\Property(property="town", type="string", example="Town"),
     *                  @OA\Property(property="county", type="string", example="County"),
     *                  @OA\Property(property="country", type="string", example="Country"),
     *                  @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *                  @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *                  @OA\Property(property="verified", type="boolean", example="true"),
     *                  @OA\Property(property="dsptk_ods_code", type="string", example="UY67FO"),
     *                  @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *                  @OA\Property(property="ce_certified", type="boolean", example="false"),
     *                  @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *                  @OA\Property(property="companies_house_no", type="string", example="12345678")
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
            Organisation::where('id', $id)->update([
                'organisation_name' => $input['organisation_name'],
                'address_1' => $input['address_1'],
                'address_2' => $input['address_2'],
                'town' => $input['town'],
                'county' => $input['county'],
                'country' => $input['country'],
                'postcode' => $input['postcode'],
                'lead_applicant_organisation_name' => $input['lead_applicant_organisation_name'],
                'lead_applicant_email' => $input['lead_applicant_email'],
                'organisation_unique_id' => $input['organisation_unique_id'],
                'applicant_names' => $input['applicant_names'],
                'funders_and_sponsors' => $input['funders_and_sponsors'],
                'sub_license_arrangements' => $input['sub_license_arrangements'],
                'verified' => $input['verified'],
                'companies_house_no' => $input['companies_house_no'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Organisation::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/organisations/{id}",
     *      summary="Edit an organisations entry",
     *      description="Edit a organisations entry",
     *      tags={"organisations"},
     *      summary="organisations@edit",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="organisations entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="organisations entry ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="organisations definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="name", type="string", example="organisations Name"),
     *              @OA\Property(property="address_1", type="string", example="123 Road"),
     *              @OA\Property(property="address_2", type="string", example="Address Two"),
     *              @OA\Property(property="town", type="string", example="Town"),
     *              @OA\Property(property="county", type="string", example="County"),
     *              @OA\Property(property="country", type="string", example="Country"),
     *              @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *              @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *              @OA\Property(property="verified", type="boolean", example="true"),
     *              @OA\Property(property="dsptk_ods_code", type="string", example="UY67FO"),
     *              @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *              @OA\Property(property="ce_certified", type="boolean", example="false"),
     *              @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *              @OA\Property(property="companies_house_no", type="string", example="12345678")
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
     *                  @OA\Property(property="name", type="string", example="organisations Name"),
     *                  @OA\Property(property="address_1", type="string", example="123 Road"),
     *                  @OA\Property(property="address_2", type="string", example="Address Two"),
     *                  @OA\Property(property="town", type="string", example="Town"),
     *                  @OA\Property(property="county", type="string", example="County"),
     *                  @OA\Property(property="country", type="string", example="Country"),
     *                  @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *                  @OA\Property(property="delegate", type="string", example="Prof. First Last"),
     *                  @OA\Property(property="verified", type="boolean", example="true"),
     *                  @OA\Property(property="dsptk_ods_code", type="string", example="UY67FO"),
     *                  @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *                  @OA\Property(property="ce_certified", type="boolean", example="false"),
     *                  @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *                  @OA\Property(property="companies_house_no", type="string", example="12345678")
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
            Organisation::where('id', $id)->update([
                'organisation_name' => $input['organisation_name'],
                'address_1' => $input['address_1'],
                'address_2' => $input['address_2'],
                'town' => $input['town'],
                'county' => $input['county'],
                'country' => $input['country'],
                'postcode' => $input['postcode'],
                'lead_applicant_organisation_name' => $input['lead_applicant_organisation_name'],
                'lead_applicant_email' => $input['lead_applicant_email'],
                'organisation_unique_id' => $input['organisation_unique_id'],
                'applicant_names' => $input['applicant_names'],
                'funders_and_sponsors' => $input['funders_and_sponsors'],
                'sub_license_arrangements' => $input['sub_license_arrangements'],
                'verified' => $input['verified'],
                'companies_house_no' => $input['companies_house_no'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Organisation::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/organisations/{id}",
     *      summary="Delete an organisations entry from the system by ID",
     *      description="Delete an organisations entry from the system",
     *      tags={"organisations"},
     *      summary="organisations@destroy",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="organisations entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="organisations entry ID",
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
            Organisation::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * No swagger, internal call
     */
    public function certifications(Request $request, int $id): JsonResponse
    {
        try {
            $counts = DB::table('organisations')
                ->select(DB::raw(
                    'dsptk_certified + ce_certified + iso_27001_certified as `count`'
                ))
                ->where('id', $id)
                ->get();

            if ($counts && count($counts) > 0) {
                return response()->json([
                    'message' => 'success',
                    'data' => $counts[0]->count,
                ], 200);
            }

            return response()->json([
                'message' => 'success',
                'data' => 0,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
            * @OA\Get(
            *      path="/api/v1/organisations/{id}/projects",
            *      summary="Return an all projects associated with an organisation",
            *      description="Return an all projects associated with an organisation (i.e. data-custodian)",
            *      tags={"organisation"},
            *      summary="organisation@getPorjects",
            *      security={{"bearerAuth":{}}},
            *
            *      @OA\Parameter(
            *         name="id",
            *         in="path",
            *         description="Organisation ID",
            *         required=true,
            *         example="1",
            *
            *         @OA\Schema(
            *            type="integer",
            *            description="Organisation ID",
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
    public function getProjects(Request $request, int $organisationId): JsonResponse
    {
        $approved = $request->query('approved', null);
        $approved = is_null($approved) ? null : filter_var($approved, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $projects = Project::searchViaRequest()
          ->applySorting()
          ->with('approvals')
          ->when(!is_null($approved), function ($query) use ($organisationId, $approved) {
              if ($approved) {
                  $query->whereHas('approvals', function ($subQuery) use ($organisationId) {
                      $subQuery->where('issuer_id', $organisationId);
                  });
              } else {
                  $query->whereDoesntHave('approvals', function ($subQuery) use ($organisationId) {
                      $subQuery->where('issuer_id', $organisationId);
                  });
              }
          })
          ->whereHas('organisations', function ($query) use ($organisationId) {
              $query->where('organisations.id', $organisationId);
          })
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
