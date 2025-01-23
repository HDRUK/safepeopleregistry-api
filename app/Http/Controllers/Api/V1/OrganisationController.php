<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Hash;
use Exception;
use RegistryManagementController as RMC;
use Carbon\Carbon;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Jobs\OrganisationIDVT;
use App\Models\Project;
use App\Models\Organisation;
use App\Models\OrganisationHasDepartment;
use App\Models\OrganisationHasSubsidiary;
use App\Models\Subsidiary;
use App\Models\User;
use App\Models\UserHasDepartments;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Organisations\EditOrganisation;
use TriggerEmail;

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
     *                  @OA\Property(property="sector_id", type="number", example="1"),
     *                  @OA\Property(property="dsptk_ods_code", type="string", example="UY67FO"),
     *                  @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *                  @OA\Property(property="ce_certified", type="boolean", example="false"),
     *                  @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *                  @OA\Property(property="companies_house_no", type="string", example="12345678"),
     *                  @OA\Property(property="charity_registration_id", type="string", example="12345678"),
     *                  @OA\Property(property="ror_id", type="string", example="05xs36f43"),
     *                  @OA\Property(property="website", type="string", example="http://www.hdruk.ac.uk"),
     *                  @OA\Property(property="smb_status", type="string", example="true"),
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

        $custodianId = $request->get('custodian_id');
        if (! $custodianId) {
            $organisations = Organisation::searchViaRequest()
                ->applySorting()
                ->with([
                    'departments',
                    'subsidiaries',
                    'approvals',
                    'permissions',
                    'files',
                    'registries',
                    'registries.user',
                    'registries.user.permissions',
                    'registries.user.approvals',
                ])->paginate((int)$this->getSystemConfig('PER_PAGE'));
        }

        return response()->json([
            'message' => 'success',
            'data' => $organisations,
        ], 200);
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
     *                  @OA\Property(property="sector_id", type="number", example="1"),
     *                  @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *                  @OA\Property(property="ce_certified", type="boolean", example="false"),
     *                  @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *                  @OA\Property(property="companies_house_no", type="string", example="12345678"),
     *                  @OA\Property(property="charity_registration_id", type="string", example="12345678"),
     *                  @OA\Property(property="ror_id", type="string", example="05xs36f43"),
     *                  @OA\Property(property="website", type="string", example="http://www.hdruk.ac.uk"),
     *                  @OA\Property(property="smb_status", type="string", example="true"),
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
            'departments',
            'subsidiaries',
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
     *              @OA\Property(property="companies_house_no", type="string", example="12345678"),
     *              @OA\Property(property="sector_id", type="number", example="1"),
     *              @OA\Property(property="charity_registration_id", type="string", example="12345678"),
     *              @OA\Property(property="ror_id", type="string", example="05xs36f43"),
     *              @OA\Property(property="website", type="string", example="http://www.hdruk.ac.uk"),
     *              @OA\Property(property="size", type="string", example="10 to 49"),
     *              @OA\Property(property="departments", type="array",
     *                  @OA\Items(type="integer"),
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
                'charity_registration_id' => $input['charity_registration_id'],
                'ror_id' => $input['ror_id'],
                'website' => $input['website'],
                'smb_status' => $input['smb_status'],
            ]);

            if (isset($input['departments'])) {
                foreach ($input['departments'] as $dept) {
                    OrganisationHasDepartment::create([
                        'organisation_id' => $organisation->id,
                        'department_id' => $dept,
                    ]);
                }
            }

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

    //Hide from swagger
    public function storeUnclaimed(Request $request): JsonResponse
    {
        try {
            $input = $request->all();
            $organisation = Organisation::create([
                'organisation_name' => '',
                'address_1' => '',
                'address_2' => '',
                'town' => '',
                'county' => '',
                'country' => '',
                'postcode' => '',
                'lead_applicant_organisation_name' => '',
                'lead_applicant_email' => $input['lead_applicant_email'],
                'password' => '',
                'organisation_unique_id' => '',
                'applicant_names' => '',
                'funders_and_sponsors' => '',
                'sub_license_arrangements' => '',
                'verified' => 0,
                'companies_house_no' => '',
                'sector_id' => 0,
                'dsptk_certified' => 0,
                'dsptk_certification_num' => '',
                'iso_27001_certified' => 0,
                'iso_27001_certification_num' => '',
                'ce_certified' => 0,
                'ce_certification_num' => '',
                'ce_plus_certified' => 0,
                'ce_plus_certification_num' => '',
                'charity_registration_id' => '',
                'ror_id' => '',
                'website' => '',
                'smb_status' => 0,
                'unclaimed' => 1
            ]);

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
     *              @OA\Property(property="sector_id", type="number", example="1"),
     *              @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *              @OA\Property(property="ce_certified", type="boolean", example="false"),
     *              @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *              @OA\Property(property="companies_house_no", type="string", example="12345678"),
     *              @OA\Property(property="charity_registration_id", type="string", example="12345678"),
     *              @OA\Property(property="ror_id", type="string", example="05xs36f43"),
     *              @OA\Property(property="website", type="string", example="http://www.hdruk.ac.uk"),
     *              @OA\Property(property="smb_status", type="string", example="true"),
     *
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
     *                  @OA\Property(property="sector_id", type="number", example="1"),
     *                  @OA\Property(property="iso_27001_certified", type="boolean", example="true"),
     *                  @OA\Property(property="ce_certified", type="boolean", example="false"),
     *                  @OA\Property(property="ce_certification_num", type="string", example="fghj63-kdhgke-736jfks-0000"),
     *                  @OA\Property(property="companies_house_no", type="string", example="12345678"),
     *                  @OA\Property(property="charity_registration_id", type="string", example="12345678"),
     *                  @OA\Property(property="ror_id", type="string", example="05xs36f43"),
     *                  @OA\Property(property="website", type="string", example="http://www.hdruk.ac.uk"),
     *                  @OA\Property(property="smb_status", type="string", example="true"),
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
                'sector_id' => $input['sector_id'],
                'charity_registration_id' => $input['charity_registration_id'],
                'ror_id' => $input['ror_id'],
                'website' => $input['website'],
                'smb_status' => $input['smb_status'],
            ]);

            if ($request->has('subsidiaries')) {
                $this->cleanSubsidiaries($id);
                foreach ($request->input('subsidiaries') as $subsidiary) {
                    $this->addSubsidiary($id, $subsidiary);
                }
            }

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
     *      summary="Edit an organisation's entry",
     *      description="Edit specific fields of an organisation's entry",
     *      tags={"organisations"},
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Organisation entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Organisation entry ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Fields to update",
     *          @OA\JsonContent(
     *              @OA\Property(property="organisation_name", type="string", example="New Name"),
     *              @OA\Property(property="address_1", type="string", example="123 Road"),
     *              @OA\Property(property="address_2", type="string", example="Address Two"),
     *              @OA\Property(property="town", type="string", example="Town"),
     *              @OA\Property(property="county", type="string", example="County"),
     *              @OA\Property(property="country", type="string", example="Country"),
     *              @OA\Property(property="postcode", type="string", example="AB12 3CD"),
     *              @OA\Property(property="lead_applicant_organisation_name", type="string", example="Lead Organisation"),
     *              @OA\Property(property="lead_applicant_email", type="string", example="lead@example.com"),
     *              @OA\Property(property="password", type="string", example="password123"),
     *              @OA\Property(property="organisation_unique_id", type="string", example="unique123"),
     *              @OA\Property(property="applicant_names", type="string", example="John Doe"),
     *              @OA\Property(property="funders_and_sponsors", type="string", example="Fund A"),
     *              @OA\Property(property="sub_license_arrangements", type="string", example="Arrangements"),
     *              @OA\Property(property="verified", type="boolean", example=true),
     *              @OA\Property(property="companies_house_no", type="string", example="12345678"),
     *              @OA\Property(property="sector_id", type="integer", example=1),
     *              @OA\Property(property="dsptk_certified", type="boolean", example=true),
     *              @OA\Property(property="dsptk_certification_num", type="string", example="CERT123"),
     *              @OA\Property(property="iso_27001_certified", type="boolean", example=true),
     *              @OA\Property(property="iso_27001_certification_num", type="string", example="ISO123"),
     *              @OA\Property(property="ce_certified", type="boolean", example=false),
     *              @OA\Property(property="ce_certification_num", type="string", example="CE123"),
     *              @OA\Property(property="charity_registration_id", type="string", example="CHARITY123"),
     *              @OA\Property(property="ror_id", type="string", example="ROR123"),
     *              @OA\Property(property="website", type="string", example="http://www.example.com"),
     *              @OA\Property(property="smb_status", type="boolean", example=true),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object")
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found")
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          ),
     *      )
     * )
     */
    public function edit(EditOrganisation $request, int $id): JsonResponse
    {
        try {
            $organisation = Organisation::find($id);

            if (!$organisation) {
                return response()->json(['message' => 'not found'], 404);
            }

            $updated = $organisation->update($request->validated());

            if ($updated) {
                if ($request->has('subsidiaries')) {
                    $this->cleanSubsidiaries($id);
                    foreach ($request->input('subsidiaries') as $subsidiary) {
                        $this->addSubsidiary($id, $subsidiary);
                    }
                }
                return response()->json(['message' => 'success', 'data' => $updated], 200);
            } else {
                return response()->json(['message' => 'Failed to update organisation'], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'error',
                'error' => $e->getMessage()
            ], 500);
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
    public function countCertifications(Request $request, int $id): JsonResponse
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
     *           @OA\JsonContent(
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
          ->when(!is_null($approved), function ($query) use ($approved) {
              if ($approved) {
                  $query->whereHas('approvals');
              } else {
                  $query->whereDoesntHave('approvals');
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

    /**
     * @OA\Get(
     *      path="/api/v1/organisations/{id}/users",
     *      summary="Return all users associated with an organisation",
     *      description="Return all users associated with an organisation",
     *      tags={"organisation"},
     *      summary="organisation@getUsers",
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
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="first_name", type="string", example="John"),
     *                          @OA\Property(property="last_name", type="string", example="Doe"),
     *                          @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                          @OA\Property(property="created_at", type="string", example="2023-06-01T12:00:00Z"),
     *                          @OA\Property(property="updated_at", type="string", example="2023-06-01T12:00:00Z")
     *                      )
     *                  ),
     *                  @OA\Property(property="first_page_url", type="string"),
     *                  @OA\Property(property="from", type="integer"),
     *                  @OA\Property(property="last_page", type="integer"),
     *                  @OA\Property(property="last_page_url", type="string"),
     *                  @OA\Property(property="next_page_url", type="string"),
     *                  @OA\Property(property="path", type="string"),
     *                  @OA\Property(property="per_page", type="integer"),
     *                  @OA\Property(property="prev_page_url", type="string"),
     *                  @OA\Property(property="to", type="integer"),
     *                  @OA\Property(property="total", type="integer")
     *              )
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=404,
     *          description="Not found response",
     *
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="not found"),
     *          )
     *      )
     * )
     */
    public function getUsers(Request $request, int $organisationId): JsonResponse
    {
        try {
            $users = User::searchViaRequest()
                ->with([
                'permissions',
                'registry',
                'registry.files',
                'pendingInvites',
                'organisation',
                'departments',
                'registry.education',
                'registry.training',
            ])->where('organisation_id', $organisationId)
              ->paginate((int)$this->getSystemConfig('PER_PAGE'));

            return response()->json([
              'message' => 'success',
              'data' => $users,
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * No swagger, internal call
     */
    public function countUsers(Request $request, int $id): JsonResponse
    {
        try {
            $count = DB::table('registry_has_organisations')
                ->select(DB::raw(
                    'COUNT(registry_id) as `count`'
                ))
                ->where('organisation_id', $id)
                ->get();

            if ($count && count($count) > 0) {
                return response()->json([
                    'message' => 'success',
                    'data' => $count[0]->count,
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
     * @OA\Post(
     *      path="/api/v1/organisations/{id}/invite_user",
     *      summary="Invites a user to org",
     *      description="Invites a user to org",
     *      tags={"organisations"},
     *      summary="organisations@invite_user",
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
     *          description="Invite definition",
     *
     *          @OA\JsonContent(
     *              @OA\Property(property="last_name", type="string", example="Smith"),
     *              @OA\Property(property="first_name", type="string", example="John"),
     *              @OA\Property(property="email", type="string", example="someone@somewhere.com"),
     *              @OA\Property(property="is_delegate", type="integer", example="1"),
     *              @OA\Property(property="department_id", type="integer", example="1"),
     *              @OA\Property(property="role", type="string", example="admin"),
     *              @OA\Property(property="user_group", type="string", example="USERS"),
     *          ),
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="integer", example="1"),
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
    public function inviteUser(Request $request, int $id): JsonResponse
    {
        try {
            $unclaimedUser = RMC::createUnclaimedUser([
                'firstname' => $request['first_name'],
                'lastname' => $request['last_name'],
                'email' => $request['email'],
                'organisation_id' => (isset($request['user_group']) && $request['user_group'] === 'ORGANISATION') ? $id : 0,
                'is_delegate' => isset($request['is_delegate'])? $request['is_delegate'] : 0,
                'user_group' => isset($request['user_group'])? $request['user_group'] : 'USERS',
                'role' => isset($request['role'])? $request['role'] : null,
            ]);

            if($request['department_id'] !== 0) {
                UserHasDepartments::create([
                    'user_id' => $unclaimedUser->id,
                    'department_id' => $request['department_id'],
                ]);
             }   

            $input = [
                'type' => 'USER',
                'to' => $unclaimedUser->id,
                'by' => $id,
                'identifier' => $request['identifier'],
            ];

            TriggerEmail::spawnEmail($input);

            return response()->json([
                'message' => 'success',
                'data' => $unclaimedUser->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    //Hide from swagger docs
    public function invite(Request $request, int $id): JsonResponse
    {
        try {
            $organisation = Organisation::where('id', $id)->first();

            $unclaimedUser = RMC::createUnclaimedUser([
                'firstname' => '',
                'lastname' => '',
                'email' => $organisation['lead_applicant_email'],
                'user_group' => 'ORGANISATIONS',
                'organisation_id' => $id
            ]);

            $input = [
                'type' => 'ORGANISATION',
                'to' => $organisation->id,
                'unclaimed_user_id' => $unclaimedUser->id,
                'by' => $id,
                'identifier' => 'organisation_invite'
            ];

            TriggerEmail::spawnEmail($input);

            return response()->json([
                'message' => 'success',
                'data' => $organisation,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * No swagger, internal call
     */
    public function countPresentProjects(Request $request, int $id): JsonResponse
    {
        try {
            $projectCount = Project::whereHas('organisations', function ($query) use ($id) {
                $query->where('organisations.id', $id);
            })
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->count();

            return response()->json(
                ['data' => $projectCount],
                200
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * No swagger, internal call
     */
    public function countPastProjects(Request $request, int $id): JsonResponse
    {
        try {
            $projectCount = Project::whereHas('organisations', function ($query) use ($id) {
                $query->where('organisations.id', $id);
            })
            ->where('start_date', '<', Carbon::now())
            ->where('end_date', '<', Carbon::now())
            ->count();

            return response()->json(
                ['data' => $projectCount],
                200
            );
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function cleanSubsidiaries(int $organisationId)
    {
        OrganisationHasSubsidiary::where('organisation_id', $organisationId)->delete();

    }

    public function addSubsidiary(int $organisationId, array $subsidiary)
    {
        if (is_null($subsidiary['name'])) {
            return;
        }
        $subsidiaryData = [
            'name' => $subsidiary['name'],
            'address_1' => $subsidiary['address']['address_1'] ?? null,
            'address_2' => $subsidiary['address']['address_1'] ?? null,
            'town' => $subsidiary['address']['town'] ?? null,
            'county' => $subsidiary['address']['county'] ?? null,
            'country' => $subsidiary['address']['country'] ?? null,
            'postcode' => $subsidiary['address']['postcode'] ?? null,
        ];

        $subsidiary = Subsidiary::updateOrCreate(
            $subsidiaryData
        );

        OrganisationHasSubsidiary::updateOrCreate(
            [
                'organisation_id' => $organisationId,
                'subsidiary_id' => $subsidiary->id
            ]
        );
    }
}