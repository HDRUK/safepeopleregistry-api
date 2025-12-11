<?php

namespace App\Http\Controllers\Api\V1;

use TriggerEmail;
use Exception;
use App\Models\User;
use App\Models\State;
use App\Models\Project;
use App\Models\Registry;
use App\Models\Affiliation;
use App\Models\Organisation;
use Illuminate\Http\Request;
use App\Traits\FilterManager;
use App\Http\Traits\Responses;
use App\Models\ProjectHasUser;
use App\Traits\CommonFunctions;
use Illuminate\Http\JsonResponse;
use App\Traits\TracksModelChanges;
use App\Models\ProjectHasCustodian;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Exceptions\NotFoundException;
use App\Models\ProjectHasSponsorship;
use App\Models\CustodianHasProjectUser;
use App\Http\Requests\Projects\GetProject;
use App\Http\Requests\Projects\DeleteProject;
use App\Http\Requests\Projects\UpdateProject;
use App\Http\Requests\Projects\GetProjectUsers;
use App\Http\Requests\Projects\UpdateProjectUser;
use App\Models\CustodianHasProjectHasSponsorship;
use App\Http\Requests\Projects\MakePrimaryContact;
use App\Http\Requests\Projects\GetValidatedProjects;
use App\Http\Requests\Projects\UpdateAllProjectUsers;
use App\Http\Requests\Projects\GetProjectByIdAndUserId;
use App\Traits\Notifications\NotificationCustodianManager;
use App\Http\Requests\Projects\GetAllUsersFlagProjectByUserId;
use App\Http\Requests\Projects\GetProjectByIdAndOrganisationId;
use App\Http\Requests\Projects\GetProjectUsersByOrganisationId;

class ProjectController extends Controller
{
    use CommonFunctions;
    use FilterManager;
    use Responses;
    use NotificationCustodianManager;
    use TracksModelChanges;

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
    public function show(GetProject $request, int $id): JsonResponse
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
     *      path="/api/v1/projects/{projectId}/users/{userId}",
     *      summary="Get project details by projectID and userID",
     *      description="Fetches project given user and project IDs.",
     *      tags={"Project"},
     *      @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          required=true,
     *          description="ID of the user",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="projectId",
     *          in="path",
     *          required=true,
     *          description="ID of the project",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved project",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Project"
     *                  )
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Project not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Project not found")
     *          )
     *      )
     * )
     */
    public function getProjectByIdAndUserId(GetProjectByIdAndUserId $request, int $projectId, int $userId): JsonResponse
    {
        try {
            $user = User::with(['registry'])->find($userId);

            if (!$user || !$user->registry) {
                return $this->NoContent();
            }

            $digiIdent = $user->registry->digi_ident;

            $project = Project::with([
                'projectDetail',
                'custodians',
                'modelState.state',
                'custodianHasProjectUser' => function ($query) use ($digiIdent) {
                    $query->whereHas('projectHasUser', function ($query2) use ($digiIdent) {
                        $query2->where('user_digital_ident', $digiIdent);
                    })
                    ->with('modelState.state');
                },
            ])->whereHas('custodianHasProjectUser.projectHasUser', function ($q) use ($digiIdent) {
                $q->where('user_digital_ident', $digiIdent);
            })->find($projectId);

            if ($project) {
                return $this->OKResponse($project);
            } else {
                return $this->NoContent();
            }
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/projects/{projectId}/organisations/{organisationId}",
     *      summary="Get project details by projectID and organisationID",
     *      description="Fetches project given organisation and project IDs.",
     *      tags={"Project"},
     *      @OA\Parameter(
     *          name="organisationId",
     *          in="path",
     *          required=true,
     *          description="ID of the organisation",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="projectId",
     *          in="path",
     *          required=true,
     *          description="ID of the project",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved project",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/User"
     *                  )
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Project not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Project not found")
     *          )
     *      )
     * )
     */
    public function GetProjectByIdAndOrganisationId(GetProjectByIdAndOrganisationId $request, int $projectId, int $organisationId): JsonResponse
    {
        $project = Project::with([
                'projectDetail',
                'custodians',
                'modelState.state',
                'custodianHasProjectOrganisation' => function ($query) use ($organisationId) {
                    $query->whereHas('projectOrganisation', function ($query2) use ($organisationId) {
                        $query2->where('organisation_id', $organisationId);
                    })
                    ->with('modelState.state');
                },
            ])->findOrFail($projectId);

        if ($project) {
            return $this->OKResponse($project);
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
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
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
    public function getProjectUsers(GetProjectUsers $request, int $id): JsonResponse
    {
        $loggedInUserId = $request->user();

        $projectUsers = ProjectHasUser::with([
            'registry.user',
            'role',
            'project',
            'project.modelState.state',
            'affiliation.modelState.state',
            'affiliation.organisation:id,organisation_name',
            'custodianHasProjectUser.modelState.state',
        ])
            ->where('project_id', $id)
            ->whereHas('registry.user', function ($query) {
                /** @phpstan-ignore-next-line */
                $query->searchViaRequest()
                    ->filterByState()
                    ->with("modelState");
            })
            ->whereHas('registry.user', function ($query) use ($loggedInUserId) {
                if ($loggedInUserId->user_group === User::GROUP_USERS) {
                    $query->where('id', $loggedInUserId->id);
                }
            })
            ->whereHas('affiliation.organisation', function ($query) {
                /** @phpstan-ignore-next-line */
                $query->searchViaRequest();
            })
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return $this->OKResponse($projectUsers);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/projects/{projectId}/organisations/{organisationId}/users",
     *      summary="Get all users by projectID and organisationID",
     *      description="Fetches users given organisation and project IDs.",
     *      tags={"Project"},
     *      @OA\Parameter(
     *          name="organisationId",
     *          in="path",
     *          required=true,
     *          description="ID of the organisation",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="projectId",
     *          in="path",
     *          required=true,
     *          description="ID of the project",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved organisation users",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/User"
     *                  )
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Organisation users not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Organisation users not found")
     *          )
     *      )
     * )
     */
    public function getProjectUsersByOrganisationId(GetProjectUsersByOrganisationId $request, int $projectId, int $organisationId): JsonResponse
    {
        $projectUsers = ProjectHasUser::with([
            'registry.user',
            'role',
            'project.modelState.state',
            'project' => function ($query) use ($projectId, $organisationId) {
                $query->where('id', $projectId)
                    ->with([
                        'custodianHasProjectOrganisation' => function ($query2) use ($organisationId) {
                            $query2->whereHas('projectOrganisation', function ($query3) use ($organisationId) {
                                $query3->where('organisation_id', $organisationId);
                            })
                            ->with('modelState.state');
                        },
                    ]);
            },
            'affiliation.organisation:id,organisation_name',
        ])
            ->where('project_id', $projectId)
            ->whereHas('registry.user', function ($query) {
                /** @phpstan-ignore-next-line */
                $query->searchViaRequest()
                    ->filterByState()
                    ->with("modelState");
            })
            ->whereHas('affiliation.organisation', function ($query) use ($organisationId) {
                /** @phpstan-ignore-next-line */
                $query->where('id', $organisationId);
            })
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return $this->OKResponse($projectUsers);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/projects/{projectId}/all_users/{userId}",
     *      summary="Get all users by projectID and userID",
     *      description="Fetches users for a project.",
     *      tags={"Project"},
     *      @OA\Parameter(
     *          name="userId",
     *          in="path",
     *          required=true,
     *          description="ID of the user",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="projectId",
     *          in="path",
     *          required=true,
     *          description="ID of the project",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="project_user_id", type="integer", example=1),
     *                      @OA\Property(property="user_id", type="integer", example=14),
     *                      @OA\Property(property="registry_id", type="integer", example=5),
     *                      @OA\Property(property="first_name", type="string", example="Harold"),
     *                      @OA\Property(property="last_name", type="string", example="Ramis"),
     *                      @OA\Property(property="professional_email", type="string", example="nlindgren@hotmail.com"),
     *                      @OA\Property(property="affiliation_id", type="integer", example=6),
     *                      @OA\Property(property="organisation_name", type="string", example="TANDY ENERGY LIMITED"),
     *                      @OA\Property(property="role", type="integer", example=1),
     *                  )
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Not authorised")
     *          )
     *      )
     * )
     */
    public function getAllUsersFlagProjectByUserId(GetAllUsersFlagProjectByUserId $request, int $id, int $userId)
    {
        $project = Project::find($id);

        if (!Gate::allows('viewProjectUserDetails', $project)) {
            return $this->ForbiddenResponse();
        };

        $user = User::where(['user_group' => User::GROUP_USERS, 'id' => $userId])
            ->with([
                'modelState',
                'registry.affiliations',
                'registry.affiliations.organisation',
                'registry.projectUsers.role',
                'registry.projectUsers.affiliation'
            ])->first();

        $idCounter = 1;

        $expandedUser = $user->registry->affiliations->map(function ($affiliation) use ($user, $id, &$idCounter) {
            return $this->formatProjectUserAffiliation($affiliation, $user, $id, $idCounter++);
        });

        return $this->OKResponse($expandedUser);
    }

    public function getAllUsersFlagProject(Request $request, int $projectId): JsonResponse
    {
        $project = Project::find($projectId);

        if (!Gate::allows('viewProjectUserDetails', $project)) {
            return $this->ForbiddenResponse();
        };

        $userProjectFilter = request()->get('user_project_filter');
        if ($userProjectFilter && !in_array(strtoupper($userProjectFilter), ['IN'])) {
            return $this->ErrorResponse('Invalid project filter.');
        }

        $users = User::searchViaRequest()
            ->where('user_group', User::GROUP_USERS)
            ->filterByState()
            ->when(strtoupper($userProjectFilter) === ProjectHasUser::USER_IN_PROJECT, function ($query) use ($projectId) {
                $query->whereHas('registry.projectUsers', function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                });
            })
            ->with([
                'modelState',
                'registry.affiliations',
                'registry.affiliations.organisation',
                'registry.projectUsers.role',
                'registry.projectUsers.affiliation'
            ])
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        $idCounter = 1;

        $expandedUsers = $users->getCollection()->flatMap(function ($user) use ($projectId, &$idCounter) {
            return $user->registry->affiliations
                ->filter(function ($affiliation) use ($user) {
                    return $user->registry->projectUsers->contains(function ($projectUser) use ($affiliation) {
                        return $projectUser->affiliation_id == $affiliation->id;
                    });
                })
                ->map(function ($affiliation) use ($user, $projectId, &$idCounter) {
                    return $this->formatProjectUserAffiliation($affiliation, $user, $projectId, $idCounter++);
                });
        });

        $paginatedResult = new \Illuminate\Pagination\LengthAwarePaginator(
            $expandedUsers,
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
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
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
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function update(UpdateProject $request, int $id): JsonResponse
    {
        try {
            $loggedInUserId = $request->user()?->id;
            $input = $request->only(app(Project::class)->getFillable());
            $project = Project::findOrFail($id);
            $before = $project->toArray();

            $project->update($input);
            $after = $project->fresh();
            $projectChanges = collect($project->getChanges())->except('updated_at')->toArray();
            $changes = $this->getProjectTrackedChanges($before, $projectChanges);

            if ($changes) {
                $this->notifyOnProjectDetailsChange($loggedInUserId, $after, $changes);
            }

            $status = $request->get('status');

            if (isset($status)) {
                if ($project->canTransitionTo($status)) {
                    $oldStatus = $project->getState();
                    $project->transitionTo($status);
                    if ($oldStatus !== $status) {
                        $this->notifyOnProjectStateChange($loggedInUserId, $id, $oldStatus, $status);
                    }
                } else {
                    return $this->BadRequestResponse();
                }
            }

            $notifySponsor = false;
            $sponsorId = $request->get('sponsor_id');

            if ($sponsorId) {
                $projectHasCustodian = ProjectHasCustodian::where('project_id', $id)->first();
                $checkProjectSponsor = ProjectHasSponsorship::where('project_id', $id)->first();

                if (is_null($checkProjectSponsor)) {
                    $notifySponsor = true;
                }

                if (!is_null($checkProjectSponsor) && (int)$sponsorId !== (int)$checkProjectSponsor->sponsor_id) {
                    $notifySponsor = true;
                    $checkProjectSponsor->delete();
                }

                $this->sponsorToProject($id, $sponsorId, $projectHasCustodian->custodian_id);

                if ($notifySponsor) {
                    $this->notifyOnAddSponsorToProject($loggedInUserId, $id, $sponsorId);
                    $this->emailOnAddSponsorToProject($loggedInUserId, $id, $sponsorId);
                }

            }

            $returnProject = Project::query()
                ->where('id', $id)
                ->with([
                    'projectHasSponsorships.sponsor',
                    'projectHasSponsorships.custodianHasProjectHasSponsorship.modelState.state',
                    ])
                ->first();
            return $this->OKResponse($returnProject);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function emailOnAddSponsorToProject($loggedInUserId, $projectId, $organisationId)
    {
        $userDelegates = User::query()
            ->where([
                'organisation_id' => $organisationId,
                'is_delegate' => 1
            ])
            ->select(['id'])
            ->get();

        foreach ($userDelegates as $userDelegate) {
            $email = [
                'type' => 'CUSTODIAN_SPONSORSHIP_REQUEST',
                'to' => $userDelegate->id,
                'by' => $loggedInUserId,
                'organisationId' => $organisationId,
                'projectId' => $projectId,
                'identifier' => 'custodian_sponsorship_request',
            ];

            TriggerEmail::spawnEmail($email);
        }

        return true;
    }

    public function sponsorToProject(int $projectId, int $sponsorId, int $custodianId)
    {
        $checkProjectSponsor = ProjectHasSponsorship::where([
            'project_id' => $projectId,
            'sponsor_id' => $sponsorId,
        ])->first();

        if (!is_null($checkProjectSponsor)) {
            return;
        }

        $projectHasSponsorship = ProjectHasSponsorship::create([
            'project_id' => $projectId,
            'sponsor_id' => $sponsorId,
        ]);

        $custodianHasProjectHasSponsorship = CustodianHasProjectHasSponsorship::create([
            'project_has_sponsorship_id' => $projectHasSponsorship->id,
            'custodian_id' => $custodianId,
        ]);

        $custodianHasProjectHasSponsorship->transitionTo(State::STATE_SPONSORSHIP_PENDING);

        return true;
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
     *          response=400,
     *          description="Invalid argument(s)",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid argument(s)")
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
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function MakePrimaryContact(MakePrimaryContact $request, int $projectId, int $registryId): JsonResponse
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

    /**
     * @OA\Put(
     *    path="/api/v1/projects/{id}/all_users",
     *    summary="Update project with all users",
     *    description="Update all users associated with a project",
     *    tags={"Project"},
     *    summary="Project@updateAllProjectUsers",
     *    security={{"bearerAuth":{}}},
     *    @OA\Parameter(
     *       name="id",
     *       in="path",
     *       description="Project entry ID",
     *       required=true,
     *       example="1",
     *       @OA\Schema(
     *          type="integer",
     *          description="Project entry ID",
     *       ),
     *    ),
     *    @OA\RequestBody(
     *        required=true,
     *        description="Project definition",
     *        @OA\JsonContent(type="object", additionalProperties=true),
     *    ),
     *    @OA\Response(
     *       response="200",
     *       description="Success response",
     *       @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="success"),
     *          @OA\Property(
     *             property="data",
     *             type="array",
     *             example="[]",
     *             @OA\Items(
     *                type="array",
     *                @OA\Items()
     *             )
     *          ),
     *       ),
     *    ),
     *    @OA\Response(
     *        response=400,
     *        description="Invalid argument(s)",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="Invalid argument(s)")
     *        ),
     *    ),
     *    @OA\Response(
     *        response=404,
     *        description="Not found response",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="not found")
     *        ),
     *    ),
     *    @OA\Response(
     *        response=500,
     *        description="Error",
     *        @OA\JsonContent(
     *            @OA\Property(property="message", type="string", example="error")
     *        )
     *    )
     * )
     */
    public function updateAllProjectUsers(UpdateAllProjectUsers $request, int $projectId): JsonResponse
    {
        try {
            $input = $request->all();
            $inputUsers = $input['users'];
            $users = collect($inputUsers);

            $registryIds = $users->pluck('registry_id')->unique();
            $registries = Registry::with('user')->whereIn('id', $registryIds)->get()->keyBy('id');

            \DB::transaction(function () use ($users, $registries, $projectId) {
                foreach ($users as $entry) {
                    $registry = $registries->get($entry['registry_id']);
                    $user = $registry->user;
                    if (!$registry || !$user) {
                        continue;
                    }

                    $digiIdent = $registry->digi_ident;
                    $affiliationId = $entry['affiliation_id'];
                    $roleId = $entry['role']['id'] ?? null;
                    $primaryContact = $entry['primary_contact'] ?? 0;


                    $roleId = $entry['role']['id'] ?? null;
                    $phu = ProjectHasUser::where('id', $entry['project_user_id'])->first();

                    if ($phu) {
                        if (is_null($roleId)) {
                            $phu->delete();
                        } else {
                            $phu->update(
                                [
                                    'project_role_id' => $roleId,
                                    'primary_contact' => $primaryContact,
                                    'affiliation_id' => $affiliationId,
                                ]
                            );
                        }
                    } elseif ($roleId) {
                        ProjectHasUser::updateOrCreate([
                            'project_id' => $projectId,
                            'user_digital_ident' => $digiIdent, // index
                            'affiliation_id' => $affiliationId,
                        ], [
                            'project_role_id' => $roleId,
                            'primary_contact' => $primaryContact,
                        ]);
                    }
                }
            });

            return $this->OKResponse(true);
        } catch (Exception $e) {
            return $this->ErrorResponse();
        }
    }

    // removed or not used
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
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="success")
     *          ),
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
     *          response=500,
     *          description="Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="error")
     *          )
     *      )
     * )
     */
    public function destroy(DeleteProject $request, int $id): JsonResponse
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
     *      path="/api/v1/projects/user/{registryId}/validated",
     *      summary="Return (approved) projects for a registry (user)",
     *      description="Return (approved) projects for a registry (user)",
     *      tags={"Projects"},
     *      summary="Project@getValidatedProjects",
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
    public function getValidatedProjects(GetValidatedProjects $request, int $registryId): JsonResponse
    {
        $digi_ident = optional(Registry::where('id', $registryId)->first())->digi_ident;

        if (!$digi_ident) {
            return response()->json([
                'message' => 'failed',
                'data' => $registryId,
                'error' => 'cannot find user in registry',
            ], 404);
        }

        request()->merge(['filter' => 'validated']);
        $projects = CustodianHasProjectUser::with(
            ['projectHasUser.project', 'modelState.state']
        )
            ->filterByState()
            ->get()
            ->pluck('projectHasUser.project')
            ->filter()
            ->unique('id')
            ->values();

        return response()->json([
            'message' => 'success',
            'data' => $projects,
        ], 200);
    }

    public function updateProjectUser(UpdateProjectUser $request, int $projectId, int $registryId): JsonResponse
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

    public function formatProjectUserAffiliation(Affiliation $affiliation, User $user, int $projectId, $idCounter): array
    {
        $matchingProjectUser = $user->registry->projectUsers
            ->first(function ($projectUser) use ($projectId, $affiliation) {
                return $projectUser->project_id == $projectId &&
                    $projectUser->affiliation_id == $affiliation->id;
            });

        return [
            'id' => $idCounter,
            'project_user_id' => $matchingProjectUser?->id,
            'user_id' => $user->id,
            'registry_id' => $user->registry_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'professional_email' => $affiliation->email,
            'affiliation_id' => $affiliation->id,
            'organisation_name' => $affiliation->organisation?->organisation_name,
            'role' => $matchingProjectUser?->role,
        ];
    }

}
