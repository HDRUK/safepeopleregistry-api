<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Traits\Responses;
use App\Traits\StateWorkflow;
use App\Models\Project;
use App\Models\Registry;
use App\Models\State;
use App\Models\ProjectHasUser;
use App\Traits\CommonFunctions;
use App\Traits\FilterManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectController extends Controller
{
    use CommonFunctions;
    use FilterManager;
    use Responses;
    use StateWorkflow;

    /**
     * @OA\Get(
     *      path="/api/v1/projects",
     *      summary="Return a list of Projects",
     *      description="Return a list of Projects",
     *      tags={"Project"},
     *      summary="Project@index",
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
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="name", type="string", example="My First Research Project"),
     *                  @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *                  @OA\Property(property="runs_to", type="string", example="2026-02-04")
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
        $projects = Project::searchViaRequest()
            ->filterByState()
            ->applySorting()
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return response()->json(
            $projects,
            200
        );
    }

    /**
     * @OA\Get(
     *      path="/api/v1/projects/users/workflow",
     *      summary="Return transitions for project users",
     *      description="Return transitions for project users",
     *      tags={"Project"},
     *      summary="Project@getUsersWorkflow",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data", type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="string"
     *                  )
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
    public function getUsersWorkflow(Request $request): JsonResponse
    {
        return $this->OKResponse($this->pickTransitions([State::STATE_FORM_RECEIVED, State::STATE_VALIDATION_IN_PROGRESS,State::STATE_VALIDATION_COMPLETE,State::STATE_MORE_USER_INFO_REQ,State::STATE_ESCALATE_VALIDATION]));
    }

    /**
     * @OA\Get(
     *      path="/api/v1/projects/{id}",
     *      summary="Return a Project entry by ID",
     *      description="Return a Project entry by ID",
     *      tags={"Project"},
     *      summary="Project@show",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
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
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="name", type="string", example="My First Research Project"),
     *                  @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *                  @OA\Property(property="runs_to", type="string", example="2026-02-04"),
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
        $project = Project::with(['projectDetail', 'custodians', 'modelState.state'])->findOrFail($id);

        if ($project) {
            return response()->json([
                'message' => 'success',
                'data' => $project,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Get(
     *      path="/api/v1/projects/{id}/users",
     *      summary="Return project users by project ID",
     *      description="Return project users by project ID",
     *      tags={"Project"},
     *      summary="Project@getProjectUsers",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="project_id", type="integer", example=1),
     *                      @OA\Property(property="user_digital_ident", type="string", example="$2y$12$IJ2LFUartH4N9xKSfxyL5ee5wdJC59aqKx180/72J3oonpw0JFiD2"),
     *                      @OA\Property(
     *                          property="registry",
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=9),
     *                          @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-03T10:17:08.000000Z"),
     *                          @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-03T10:17:08.000000Z"),
     *                          @OA\Property(property="verified", type="boolean", example=false),
     *                          @OA\Property(
     *                              property="user",
     *                              type="object",
     *                              @OA\Property(property="id", type="integer", example=18),
     *                              @OA\Property(property="first_name", type="string", example="Tobacco"),
     *                              @OA\Property(property="last_name", type="string", example="Dave"),
     *                              @OA\Property(property="email", type="string", example="tobacco.dave@dodgydomain.com"),
     *                              @OA\Property(property="registry_id", type="integer", example=9),
     *                              @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-03T10:17:06.000000Z"),
     *                              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-03T10:17:08.000000Z"),
     *                              @OA\Property(property="user_group", type="string", example="USERS"),
     *                              @OA\Property(property="consent_scrape", type="boolean", example=false),
     *                              @OA\Property(property="public_opt_in", type="boolean", example=0)
     *                          ),
     *                          @OA\Property(
     *                              property="organisations",
     *                              type="array",
     *                              @OA\Items(
     *                                  @OA\Property(property="id", type="integer", example=3),
     *                                  @OA\Property(property="organisation_name", type="string", example="TANDY ENERGY LIMITED")
     *                              )
     *                          ),
     *                           @OA\Property(
     *                               property="affiliation",
     *                               type="object",
     *                               nullable=true,
     *                               @OA\Property(property="relationship", type="string", example="employee"),
     *                               @OA\Property(property="from", type="string", example="25/01/1999"),
     *                               @OA\Property(property="to", type="string", example="01/12/2010"),
     *                               @OA\Property(property="department", type="string", example="Research & Development"),
     *                               @OA\Property(property="role", type="string", example="Principal Investigator (PI)"),
     *                               @OA\Property(property="email", type="string", example="professional.email@email.com"),
     *                               @OA\Property(property="ror", type="string", example="0hgyje84")
     *                           )
     *                      ),
     *                      @OA\Property(
     *                          property="role",
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="Principal Investigator (PI)")
     *                      )
     *                  )
     *              )
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
    public function getProjectUsers(Request $request, int $projectId): JsonResponse
    {
        $projectUsers = ProjectHasUser::with([
            'registry.user',
            'role',
            'affiliation.organisation:id,organisation_name',
        ])
            ->where('project_id', $projectId)
            ->whereHas('registry.user', function ($query) {
                /** @phpstan-ignore-next-line */
                $query->searchViaRequest()
                    ->filterByState()
                    ->with("modelState");
            })
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return $this->OKResponse($projectUsers);
    }


    public function getAllUsersFlagProject(Request $request, int $projectId): JsonResponse
    {
        $users = User::searchViaRequest()
            ->where('user_group', User::GROUP_USERS)
            ->filterByState()
            ->with([
                'modelState',
                'registry.affiliations',
                'registry.affiliations.organisation',
                'registry.projectUsers.role',
                'registry.projectUsers.affiliation'
            ])
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        $idCounter = 1;
        /** @phpstan-ignore-next-line */
        $expandedUsers = $users->flatMap(function ($user) use ($projectId, &$idCounter) {
            // LS - Even though the return types match, phpstan sees them as not covariant.
            /** @phpstan-ignore-next-line */
            return $user->registry->affiliations->map(function ($affiliation) use ($user, $projectId, &$idCounter) {

                $matchingProjectUser = $user->registry->projectUsers
                    ->first(function ($projectUser) use ($projectId, $affiliation) {
                        return $projectUser->project_id == $projectId &&
                            $projectUser->affiliation_id == $affiliation->id;
                    });


                return [
                    'id' => $idCounter++,
                    'user_id' => $user->id,
                    'registry_id' => $user->registry_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'affiliation_id' => $affiliation->id,
                    'organisation_name' => $affiliation->organisation->organisation_name,
                    'role' => $matchingProjectUser?->role,
                ];
            });
        });

        $paginatedResult = new LengthAwarePaginator(
            $expandedUsers->values()->all(),
            $users->total(),
            $users->perPage(),
            $users->currentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );


        return $this->OKResponse($paginatedResult);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/projects",
     *      summary="Create a Project entry",
     *      description="Create a Project entry",
     *      tags={"Project"},
     *      summary="Project@store",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Project definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="name", type="string", example="My First Research Project"),
     *              @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *              @OA\Property(property="runs_to", type="string", example="2026-02-04")
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
     *                  @OA\Property(property="message", type="string", example="success"),
     *                  @OA\Property(property="data", type="integer", example="1")
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
            $input = $request->only(app(Project::class)->getFillable());
            $project = Project::create($input);

            if ($project) {
                $project->setState(State::STATE_PROJECT_PENDING);
            }

            return response()->json([
                'message' => 'success',
                'data' => $project->id,
            ], 201);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/projects/{id}",
     *      summary="Update a Project entry",
     *      description="Update a Project entry",
     *      tags={"Project"},
     *      summary="Project@update",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Project definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="name", type="string", example="My First Research Project"),
     *              @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *              @OA\Property(property="runs_to", type="string", example="2026-02-04")
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
     *                  @OA\Property(property="message", type="string", example="success"),
     *                  @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="name", type="string", example="My First Research Project"),
     *                  @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *                  @OA\Property(property="runs_to", type="string", example="2026-02-04"),
     *                  @OA\Property(property="status", type="string", example="approved")
     *              ),
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
            $input = $request->only(app(Project::class)->getFillable());
            $project = Project::findOrFail($id);

            if (!is_null($project)) {
                $project->update($input);
                $status = $request->get('status');

                if (isset($status)) {
                    if ($project->canTransitionTo($status)) {
                        $project->transitionTo($status);
                    } else {
                        return $this->BadRequestResponse();
                    }
                }

                return $this->OKResponse(Project::where('id', $id)->first());
            }

            return $this->NotFoundResponse();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/projects/{id}",
     *      summary="Update a Project entry",
     *      description="Update a Project entry",
     *      tags={"Project"},
     *      summary="Project@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Project definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="name", type="string", example="My First Research Project"),
     *              @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *              @OA\Property(property="runs_to", type="string", example="2026-02-04")
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
     *                  @OA\Property(property="message", type="string", example="success"),
     *                  @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example="123"),
     *                  @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *                  @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *                  @OA\Property(property="name", type="string", example="My First Research Project"),
     *                  @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *                  @OA\Property(property="runs_to", type="string", example="2026-02-04"),
     *                  @OA\Property(property="status", type="string", example="approved")
     *              ),
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
            $input = $request->only(app(Project::class)->getFillable());
            $project = Project::findOrFail($id);
            $project->update($input);

            return response()->json([
                'message' => 'success',
                'data' => Project::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/projects/{id}/users/{registryId}/primary_contact",
     *      summary="Make user a primary contact",
     *      description="Make user a primary contact",
     *      tags={"Project"},
     *      summary="Project@edit",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry ID",
     *         ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Project definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="primary_contact", type="integer", example="1"),
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
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="project_id", type="integer", example=1),
     *                      @OA\Property(property="user_digital_ident", type="string", example="$2y$12$IJ2LFUartH4N9xKSfxyL5ee5wdJC59aqKx180/72J3oonpw0JFiD2"),
     *                      @OA\Property(
     *                          property="registry",
     *                          type="object",
     *                          @OA\Property(property="id", type="integer", example=9),
     *                          @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-03T10:17:08.000000Z"),
     *                          @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-03T10:17:08.000000Z"),
     *                          @OA\Property(property="verified", type="boolean", example=false),
     *                          @OA\Property(
     *                              property="user",
     *                              type="object",
     *                              @OA\Property(property="id", type="integer", example=18),
     *                              @OA\Property(property="first_name", type="string", example="Tobacco"),
     *                              @OA\Property(property="last_name", type="string", example="Dave"),
     *                              @OA\Property(property="email", type="string", example="tobacco.dave@dodgydomain.com"),
     *                              @OA\Property(property="registry_id", type="integer", example=9),
     *                              @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-03T10:17:06.000000Z"),
     *                              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-03T10:17:08.000000Z"),
     *                              @OA\Property(property="user_group", type="string", example="USERS"),
     *                              @OA\Property(property="consent_scrape", type="boolean", example=false),
     *                              @OA\Property(property="public_opt_in", type="boolean", example=0)
     *                          ),
     *                      ),
     *                  )
     *              )
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
    public function makePrimaryContact(Request $request, int $projectId, int $registryId): JsonResponse
    {
        try {
            $input = $request->all();

            $digi_ident = optional(Registry::where('id', $registryId)->first())->digi_ident;

            if (isset($digi_ident)) {
                $projectHasUser = ProjectHasUser::where('project_id', $projectId)->where('user_digital_ident', $digi_ident);

                if ($projectHasUser->first() !== null) {
                    $projectHasUser->update([
                        'primary_contact' => $input['primary_contact']
                    ]);


                    $project = Project::findOrFail($projectId);
                    $projectUsers = $project->projectUsers()->with([
                        'registry.user',
                        'role'
                    ])->whereHas('registry.user', function ($query) use ($digi_ident) {
                        $query->where('digi_ident', $digi_ident);
                    })->first();

                    return $this->OKResponse($projectUsers);
                }
            }

            return $this->NotFoundResponse();
        } catch (Exception $e) {
            return $this->ErrorResponse();
        }
    }

    public function updateAllProjectUsers(Request $request, int $projectId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'users' => 'required|array',
            ]);

            $registryIds = collect($validated['users'])->pluck('registry_id')->unique();
            $registries = Registry::with('user')->whereIn('id', $registryIds)->get()->keyBy('id');

            $results = [];
            $deletes = [];

            foreach ($validated['users'] as $entry) {

                $registry = $registries->get($entry['registry_id']);
                if (!$registry || !$registry->user) {
                    continue;
                }

                $digiIdent = $registry->digi_ident;
                $affiliationId = $entry['affiliation_id'];
                $roleId = isset($entry['role']) ? $entry['role']['id'] ?? null : null;

                // Refactor candidate..
                // - code is really slow
                // - should be cleaner with firstOrNew and/or updateOrCreate
                // - laravel doesnt like this though as it's a pivot table
                // - dont think ProjectHasUser should be a pivot table as it's
                //   a three-way pivot between project/registry/affiliation
                $projectUserQuery = ProjectHasUser::where([
                    'project_id' => $projectId,
                    'user_digital_ident' => $digiIdent,
                    'affiliation_id' => $affiliationId,
                ]);

                if (!$projectUserQuery->exists()) {
                    $projectUser = ProjectHasUser::create([
                        'project_id' => $projectId,
                        'user_digital_ident' => $digiIdent,
                        'affiliation_id' => $affiliationId,
                    ]);
                }

                if (is_null($roleId)) {
                    $projectUserQuery->delete();
                } else {
                    $updateData = [
                        'project_role_id' => $roleId,
                    ];
                    if (isset($entry['primary_contact'])) {
                        $updateData['primary_contact'] = $entry['primary_contact'];
                    }

                    $projectUserQuery->update($updateData);

                    $results[] = $projectUserQuery->get();
                }
            }

            return $this->OKResponse($results);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }


    public function addProjectUser(Request $request, int $projectId, int $registryId): JsonResponse
    {
        $validated = $request->validate([
            'project_role_id' => 'required|integer|exists:project_roles,id',
            'affiliation_id' => 'required|integer|exists:affiliations,id',
            'primary_contact' => 'nullable|boolean',
        ]);

        try {
            $registry = Registry::with('user')->find($registryId);

            if (!$registry || !$registry->user) {
                return $this->BadRequestResponse();
            }

            $projectHasUser = ProjectHasUser::create([
                'project_id' => $projectId,
                'user_digital_ident' => $registry->digi_ident,
                'project_role_id' => $validated['project_role_id'],
                'affiliation_id' => $validated['affiliation_id'],
                'primary_contact' => $validated['primary_contact'] ?? false,
            ]);

            return $this->CreatedResponse($projectHasUser);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/projects/{id}",
     *      summary="Delete a Project entry from the system by ID",
     *      description="Delete a Project entry from the system",
     *      tags={"Project"},
     *      summary="Project@destroy",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
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
            Project::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *      path="/api/v1/projects/user/{registryId}/approved",
     *      summary="Return (approved) projects for a registry (user)",
     *      description="Return (approved) projects for a registry (user)",
     *      tags={"Projects"},
     *      summary="Project@getApprovedProjects",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Registry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry ID",
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
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="name", type="string", example="My First Research Project"),
     *                  @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *                  @OA\Property(property="runs_to", type="string", example="2026-02-04"),
     *                  @OA\Property(property="affiliate_id", type="integer", example="2")
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
    public function getApprovedProjects(Request $request, int $registryId): JsonResponse
    {
        $digi_ident = optional(Registry::where('id', $registryId)->first())->digi_ident;

        if (!$digi_ident) {
            return response()->json([
                'message' => 'failed',
                'data' => $registryId,
                'error' => 'cannot find user in registry',
            ], 404);
        }

        $projects = ProjectHasUser::where('user_digital_ident', $digi_ident)
            ->with('project')
            ->whereHas('project.custodians', function ($query) {
                $query->where('approved', true);
            })
            ->get()
            ->pluck('project');

        return response()->json([
            'message' => 'success',
            'data' => $projects,
        ], 200);
    }

    public function updateProjectUser(Request $request, int $projectId, int $registryId): JsonResponse
    {
        $validated = $request->validate([
            'project_role_id' => 'nullable|integer|exists:project_roles,id',
            'primary_contact' => 'nullable|boolean',
            'affiliation_id' => 'nullable|integer|exists:affiliations,id',
        ]);

        $digiIdent = optional(Registry::find($registryId))->digi_ident;

        if (!$digiIdent) {
            return $this->NotFoundResponse();
        }

        $projectUser = ProjectHasUser::where('project_id', $projectId)
            ->where('user_digital_ident', $digiIdent)
            ->first();

        if (!$projectUser) {
            return $this->NotFoundResponse();
        }

        $projectUser->update($validated);

        return $this->OKResponse($projectUser);
    }


    /**
     * @OA\Delete(
     *      path="/api/v1/projects/{projectId}/users/{registryId}",
     *      summary="Delete a user from a project",
     *      description="Delete a user from a project",
     *      tags={"Projects"},
     *      summary="Project@deleteUserFromProject",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *         name="projectId",
     *         in="path",
     *         description="Project ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Project ID",
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="registryId",
     *         in="path",
     *         description="Registry ID",
     *         required=true,
     *         example="1",
     *         @OA\Schema(
     *            type="integer",
     *            description="Registry ID",
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="failed",
     *      )
     * )
     */
    public function deleteUserFromProject(Request $request, int $projectId, int $registryId): JsonResponse
    {
        try {
            $digi_ident = optional(Registry::where('id', $registryId)->first())->digi_ident;
            $data = ProjectHasUser::where('project_id', $projectId)->where('user_digital_ident', $digi_ident);

            if ($data->first() !== null) {
                $data->delete();

                return $this->OKResponse(null);
            }

            return $this->NotFoundResponse();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
