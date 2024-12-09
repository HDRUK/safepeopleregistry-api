<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Traits\CommonFunctions;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use CommonFunctions;

    /**
     * @OA\Get(
     *      path="/api/v1/projects",
     *      summary="Return a list of Projects",
     *      description="Return a list of Projects",
     *      tags={"Project"},
     *      summary="Project@index",
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
     *                  @OA\Property(property="registry_id", type="integer", example="1"),
     *                  @OA\Property(property="name", type="string", example="My First Research Project"),
     *                  @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *                  @OA\Property(property="runs_to", type="string", example="2026-02-04")
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
        $projects = Project::searchViaRequest()
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
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
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
        $project = Project::findOrFail($id);
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
    *      path="/api/v1/projects/{id/users",
    *      summary="Return project users by project ID",
    *      description="Return project users by project ID",
    *      tags={"Project"},
    *      summary="Project@getProjectUsers",
    *      security={{"bearerAuth":{}}},
    *
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
    *
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
    *                          @OA\Property(
    *                              property="employment",
    *                              type="object",
    *                              nullable=true,
    *                              @OA\Property(property="id", type="integer", example=1),
    *                              @OA\Property(property="employer_name", type="string", example="Demo Employer Name"),
    *                              @OA\Property(property="from", type="string", format="date", example="1977-06-08"),
    *                              @OA\Property(property="to", type="string", format="date", example="2004-01-05"),
    *                              @OA\Property(property="department", type="string", example="Rerum animi."),
    *                              @OA\Property(property="role", type="string", example="Dolorem sit ratione."),
    *                              @OA\Property(property="employer_address", type="string", example="8164 Krajcik Harbors Apt. 117\nSouth Rosemarie, IA 97953"),
    *                              @OA\Property(property="ror", type="string", example="https://emmerich.com/aperiam-esse-quia-qui-dolorum-architecto-earum-aspernatur.html")
    *                          )
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
    public function getProjectUsers(Request $request, int $id): JsonResponse
    {
        $project = Project::with([
            'projectUsers.registry.user',
            'projectUsers.registry.organisations' => function ($query) {
                return $query->select(['id','organisation_name']);
            },
            'projectUsers.registry.employment',
            'projectUsers.role'
            ])->select(['id'])->findOrFail($id);

        if ($project) {
            return response()->json([
                'message' => 'success',
                'data' => $project->projectUsers,
            ], 200);
        }

        throw new NotFoundException();
    }

    /**
     * @OA\Post(
     *      path="/api/v1/projects",
     *      summary="Create a Project entry",
     *      description="Create a Project entry",
     *      tags={"Project"},
     *      summary="Project@store",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Project definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="name", type="string", example="My First Research Project"),
     *              @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *              @OA\Property(property="runs_to", type="string", example="2026-02-04")
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
     *                  @OA\Property(property="message", type="string", example="success"),
     *                  @OA\Property(property="data", type="integer", example="1")
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
            $project = Project::create([
                'unique_id' => $input['unique_id'],
                'title' => $input['title'],
                'lay_summary' => $input['lay_summary'],
                'public_benefit' => $input['public_benefit'],
                'request_category_type' => $input['request_category_type'],
                'technical_summary' => $input['technical_summary'],
                'other_approval_committees' => $input['other_approval_committees'],
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $project->id,
            ], 201);
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
     *      summary="Project@update",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Project definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="name", type="string", example="My First Research Project"),
     *              @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *              @OA\Property(property="runs_to", type="string", example="2026-02-04")
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
     *                  @OA\Property(property="message", type="string", example="success"),
     *                  @OA\Property(property="data", type="object",
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
            $project = Project::where('id', $id)->update([
                'unique_id' => $input['unique_id'],
                'title' => $input['title'],
                'lay_summary' => $input['lay_summary'],
                'public_benefit' => $input['public_benefit'],
                'request_category_type' => $input['request_category_type'],
                'technical_summary' => $input['technical_summary'],
                'other_approval_committees' => $input['other_approval_committees'],
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
            ]);

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
     *      path="/api/v1/projects/{id}",
     *      summary="Update a Project entry",
     *      description="Update a Project entry",
     *      tags={"Project"},
     *      summary="Project@edit",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
     *         ),
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *          description="Project definition",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="id", type="integer", example="123"),
     *              @OA\Property(property="created_at", type="string", example="2024-02-04 12:00:00"),
     *              @OA\Property(property="updated_at", type="string", example="2024-02-04 12:01:00"),
     *              @OA\Property(property="registry_id", type="integer", example="1"),
     *              @OA\Property(property="name", type="string", example="My First Research Project"),
     *              @OA\Property(property="public_benefit", type="string", example="A public benefit statement"),
     *              @OA\Property(property="runs_to", type="string", example="2026-02-04")
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
     *                  @OA\Property(property="message", type="string", example="success"),
     *                  @OA\Property(property="data", type="object",
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
            $project = Project::where('id', $id)->update([
                'unique_id' => $input['unique_id'],
                'title' => $input['title'],
                'lay_summary' => $input['lay_summary'],
                'public_benefit' => $input['public_benefit'],
                'request_category_type' => $input['request_category_type'],
                'technical_summary' => $input['technical_summary'],
                'other_approval_committees' => $input['other_approval_committees'],
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
            ]);

            return response()->json([
                'message' => 'success',
                'data' => Project::where('id', $id)->first(),
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
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
     *
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project entry ID",
     *         required=true,
     *         example="1",
     *
     *         @OA\Schema(
     *            type="integer",
     *            description="Project entry ID",
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
            Project::where('id', $id)->delete();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
