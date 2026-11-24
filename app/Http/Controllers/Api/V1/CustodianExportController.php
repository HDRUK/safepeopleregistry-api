<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Custodians\ExportCustodianData;
use App\Http\Resources\CustodianExport\ExportProjectResource;
use App\Http\Resources\CustodianExport\ExportUserResource;
use App\Http\Traits\Responses;
use App\Models\Custodian;
use App\Models\Project;
use App\Models\Registry;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CustodianExportController extends Controller
{
    use CommonFunctions;
    use Responses;

    /**
     * @OA\Get(
     *      path="/api/v1/custodian/export/projects",
     *      operationId="custodianExportProjects",
     *      tags={"Custodian Export"},
     *      summary="CustodianExport@projects",
     *      description="Returns every project the calling Custodian is on, paginated. Requests are signed: see docs/ARCHITECTURE/custodian-export-api.md.",
     *      @OA\Parameter(name="x-client-id", in="header", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="x-timestamp", in="header", required=true, description="Unix seconds, within 300s of server time", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="x-signature", in="header", required=true, description="base64 HMAC-SHA256 of '{timestamp}\n{METHOD}\n{path}\n{sorted query}', keyed with the Custodian's unique_identifier", @OA\Schema(type="string")),
     *      @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *      @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="total", type="integer", example=1),
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="unique_id", type="string"),
     *                          @OA\Property(property="title", type="string"),
     *                          @OA\Property(property="start_date", type="string", nullable=true),
     *                          @OA\Property(property="end_date", type="string", nullable=true),
     *                          @OA\Property(property="status", type="object", nullable=true,
     *                              @OA\Property(property="slug", type="string", example="approved"),
     *                              @OA\Property(property="name", type="string", example="Approved")
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(response=400, description="Invalid argument(s)"),
     *      @OA\Response(response=401, description="Missing, stale or invalid signature, or the Custodian is disabled"),
     *      @OA\Response(response=429, description="Rate limit exceeded")
     * )
     */
    public function projects(ExportCustodianData $request): JsonResponse
    {
        $custodian = $this->custodian($request);

        $projects = $custodian->projects()
            ->with('modelState.state')
            ->orderBy('projects.id')
            ->paginate($this->perPage($request));

        $this->audit($custodian, 'projects', $projects);

        return $this->OKResponse(
            $projects->through(fn (Project $project) => (new ExportProjectResource($project))->resolve($request))
        );
    }

    /**
     * @OA\Get(
     *      path="/api/v1/custodian/export/users",
     *      operationId="custodianExportUsers",
     *      tags={"Custodian Export"},
     *      summary="CustodianExport@users",
     *      description="Returns every user on the calling Custodian's projects with their profile and the validation status of each of those projects, paginated. Contact details and identity documents are never included. Requests are signed: see docs/ARCHITECTURE/custodian-export-api.md.",
     *      @OA\Parameter(name="x-client-id", in="header", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="x-timestamp", in="header", required=true, description="Unix seconds, within 300s of server time", @OA\Schema(type="integer")),
     *      @OA\Parameter(name="x-signature", in="header", required=true, description="base64 HMAC-SHA256 of '{timestamp}\n{METHOD}\n{path}\n{sorted query}', keyed with the Custodian's unique_identifier", @OA\Schema(type="string")),
     *      @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", minimum=1)),
     *      @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", minimum=1, maximum=100)),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="total", type="integer", example=1),
     *                  @OA\Property(property="data", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="digi_ident", type="string"),
     *                          @OA\Property(property="first_name", type="string"),
     *                          @OA\Property(property="last_name", type="string"),
     *                          @OA\Property(property="orcid", type="string", nullable=true, example="0000-0002-1825-0097"),
     *                          @OA\Property(property="identity_verified", type="boolean"),
     *                          @OA\Property(property="affiliations", type="array", description="Every affiliation the user has had, past and present", @OA\Items(
     *                              @OA\Property(property="id", type="integer", example=12),
     *                              @OA\Property(property="organisation_name", type="string"),
     *                              @OA\Property(property="relationship", type="string"),
     *                              @OA\Property(property="role", type="string"),
     *                              @OA\Property(property="department", type="string", nullable=true),
     *                              @OA\Property(property="from", type="string", nullable=true),
     *                              @OA\Property(property="to", type="string", nullable=true),
     *                              @OA\Property(property="current_employer", type="boolean"),
     *                              @OA\Property(property="status", type="object", nullable=true)
     *                          )),
     *                          @OA\Property(property="trainings", type="array", @OA\Items(
     *                              @OA\Property(property="training_name", type="string"),
     *                              @OA\Property(property="provider", type="string"),
     *                              @OA\Property(property="awarded_at", type="string", nullable=true),
     *                              @OA\Property(property="expires_at", type="string", nullable=true),
     *                              @OA\Property(property="pro_registration", type="boolean")
     *                          )),
     *                          @OA\Property(property="accreditations", type="array", @OA\Items(
     *                              @OA\Property(property="associated_organisation_name", type="string"),
     *                              @OA\Property(property="issue_date", type="string", nullable=true),
     *                              @OA\Property(property="expiry_date", type="string", nullable=true)
     *                          )),
     *                          @OA\Property(property="projects", type="array", @OA\Items(
     *                              @OA\Property(property="project_id", type="integer"),
     *                              @OA\Property(property="project_unique_id", type="string"),
     *                              @OA\Property(property="project_title", type="string"),
     *                              @OA\Property(property="project_role", type="string", nullable=true),
     *                              @OA\Property(property="validation_status", type="object", nullable=true,
     *                                  @OA\Property(property="slug", type="string", example="validation_complete"),
     *                                  @OA\Property(property="name", type="string", example="Validation Complete")
     *                              ),
     *                              @OA\Property(property="affiliation", type="object", nullable=true, description="The affiliation linking the user to this project, in the same shape as an entry in affiliations. Null when no affiliation was recorded for the link.",
     *                                  @OA\Property(property="id", type="integer", example=12),
     *                                  @OA\Property(property="organisation_name", type="string"),
     *                                  @OA\Property(property="relationship", type="string"),
     *                                  @OA\Property(property="role", type="string"),
     *                                  @OA\Property(property="department", type="string", nullable=true),
     *                                  @OA\Property(property="from", type="string", nullable=true),
     *                                  @OA\Property(property="to", type="string", nullable=true),
     *                                  @OA\Property(property="current_employer", type="boolean"),
     *                                  @OA\Property(property="status", type="object", nullable=true)
     *                              )
     *                          ))
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(response=400, description="Invalid argument(s)"),
     *      @OA\Response(response=401, description="Missing, stale or invalid signature, or the Custodian is disabled"),
     *      @OA\Response(response=429, description="Rate limit exceeded")
     * )
     */
    public function users(ExportCustodianData $request): JsonResponse
    {
        $custodian = $this->custodian($request);
        $onlyThisCustodian = fn ($query) => $query->where('custodian_id', $custodian->id);
        $linkedToThisCustodian = fn ($query) => $query
            ->whereHas('project')
            ->whereHas('custodianHasProjectUser', $onlyThisCustodian);

        $users = Registry::query()
            ->whereHas('projectUsers', $linkedToThisCustodian)
            ->with([
                'user:id,registry_id,first_name,last_name,orc_id',
                'identity:id,registry_id,idvt_success',
                'affiliations.organisation:id,organisation_name',
                'affiliations.modelState.state',
                'trainings',
                'accreditations',
                'projectUsers' => $linkedToThisCustodian,
                'projectUsers.project:id,unique_id,title',
                'projectUsers.role:id,name',
                'projectUsers.affiliation.organisation:id,organisation_name',
                'projectUsers.affiliation.modelState.state',
                'projectUsers.custodianHasProjectUser' => $onlyThisCustodian,
                'projectUsers.custodianHasProjectUser.modelState.state',
            ])
            ->orderBy('id')
            ->paginate($this->perPage($request));

        $this->audit($custodian, 'users', $users);

        return $this->OKResponse(
            $users->through(fn (Registry $registry) => (new ExportUserResource($registry))->resolve($request))
        );
    }

    private function custodian(Request $request): Custodian
    {
        $custodian = $request->attributes->get('custodian');
        if (!$custodian instanceof Custodian) {
            abort(401);
        }

        return $custodian;
    }

    private function perPage(Request $request): int
    {
        return $request->integer('per_page', (int) $this->getSystemConfig('PER_PAGE'));
    }

    private function audit(Custodian $custodian, string $endpoint, LengthAwarePaginator $page): void
    {
        Log::info('CustodianExport', [
            'custodian_id' => $custodian->id,
            'endpoint' => $endpoint,
            'page' => $page->currentPage(),
            'per_page' => $page->perPage(),
            'count' => $page->count(),
        ]);
    }
}
