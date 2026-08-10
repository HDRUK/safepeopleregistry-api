<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Affiliation;
use App\Models\Endorsement;
use App\Models\History;
use App\Models\Identity;
use App\Models\Infringement;
use App\Models\Project;
use App\Models\Registry;
use App\Models\Training;
use App\Models\User;
use App\Models\RegistryHasTraining;
use App\Models\Custodian;
use App\Models\ProjectHasUser;
use App\Models\CustodianHasProjectUser;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class QueryController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/v1/query",
     *      operationId="queryQuery",
     *      summary="Query the registry by Digital Identifier",
     *      description="Query the registry by Digital Identifier. Authenticated via x-client-id/x-signature headers (Custodian client credential + HMAC-signed payload), not a bearer token.",
     *      tags={"Query"},
     *      summary="Query@query",
     *      @OA\Parameter(
     *          name="x-client-id",
     *          in="header",
     *          required=true,
     *          description="Custodian client ID used to authenticate the requesting custodian",
     *          @OA\Schema(type="string", example="8f14e45f-ceea-467e-adc1-0000example")
     *      ),
     *      @OA\Parameter(
     *          name="x-signature",
     *          in="header",
     *          required=true,
     *          description="HMAC signature of the raw request body, signed with the custodian's unique identifier",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Query definition",
     *          @OA\JsonContent(
     *              @OA\Property(property="ident", type="string", example="$2y$12$V6SSFQLyQDQRZxvz.Tswa.HA.ixJIXofs7.omitted", description="The Registry's digi_ident value")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorised - missing or unrecognised x-client-id header",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="no known custodian matches the credentials provided")
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
     *              @OA\Property(property="message", type="string", example="success"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="user", type="object",
     *                      description="The User record linked to the matched Registry",
     *                      @OA\Property(property="id", type="integer", example=10),
     *                      @OA\Property(property="first_name", type="string", example="Dan"),
     *                      @OA\Property(property="last_name", type="string", example="Ackroyd"),
     *                      @OA\Property(property="name", type="string", example="Dan Ackroyd"),
     *                      @OA\Property(property="registry_id", type="integer", example=1),
     *                      @OA\Property(property="created_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                      @OA\Property(property="updated_at", type="string", example="2024-03-12T13:11:55.000000Z"),
     *                      @OA\Property(property="user_group", type="string", example="USERS"),
     *                      @OA\Property(property="consent_scrape", type="boolean", example=false),
     *                      @OA\Property(property="orc_id", type="string", nullable=true, example=null),
     *                      @OA\Property(property="unclaimed", type="integer", example=0),
     *                      @OA\Property(property="feed_source", type="string", nullable=true, example=null),
     *                      @OA\Property(property="public_opt_in", type="integer", example=0),
     *                      @OA\Property(property="organisation_id", type="integer", example=0),
     *                      @OA\Property(property="orcid_scanning", type="boolean", example=false),
     *                      @OA\Property(property="orcid_scanning_completed_at", type="string", nullable=true, example=null),
     *                      @OA\Property(property="is_delegate", type="integer", example=0),
     *                      @OA\Property(property="is_org_admin", type="integer", example=0),
     *                      @OA\Property(property="custodian_id", type="integer", nullable=true, example=null),
     *                      @OA\Property(property="custodian_user_id", type="integer", nullable=true, example=null),
     *                      @OA\Property(property="role", type="string", nullable=true, example=null),
     *                      @OA\Property(property="location", type="string", nullable=true, example=null),
     *                      @OA\Property(property="t_and_c_agreed", type="boolean", example=true),
     *                      @OA\Property(property="t_and_c_agreement_date", type="string", nullable=true, example="2024-03-12 13:11:55"),
     *                      @OA\Property(property="is_sro", type="boolean", example=false),
     *                      @OA\Property(property="invited_by", type="integer", nullable=true, example=null),
     *                      @OA\Property(property="status", type="string", example="registered"),
     *                      @OA\Property(property="evaluation", type="string", nullable=true, example=null),
     *                      @OA\Property(property="identity", ref="#/components/schemas/Identity", nullable=true)
     *                  ),
     *                  @OA\Property(property="registry", type="object",
     *                      description="The matched Registry record",
     *                      allOf={
     *                          @OA\Schema(ref="#/components/schemas/Registry"),
     *                          @OA\Schema(
     *                              @OA\Property(property="training", type="array",
     *                                  description="Training records linked to the registry",
     *                                  @OA\Items(ref="#/components/schemas/Training")
     *                              ),
     *                              @OA\Property(property="history", type="array",
     *                                  description="History records linked to the registry, each with its related affiliation and project",
     *                                  @OA\Items(
     *                                      allOf={
     *                                          @OA\Schema(ref="#/components/schemas/History"),
     *                                          @OA\Schema(
     *                                              @OA\Property(property="affiliation", ref="#/components/schemas/Affiliation", nullable=true),
     *                                              @OA\Property(property="project", ref="#/components/schemas/Project", nullable=true)
     *                                          )
     *                                      }
     *                                  )
     *                              )
     *                          )
     *                      }
     *                  ),
     *                  @OA\Property(property="projects", type="array",
     *                      description="Projects the queried user is linked to, scoped to the requesting custodian",
     *                      @OA\Items(
     *                          @OA\Property(property="project_id", type="integer", example=1),
     *                          @OA\Property(property="project_title", type="string", example="Project Title"),
     *                          @OA\Property(property="project_user_validation_status", type="string", nullable=true, example="ValidationComplete")
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

        $custodianKey = $request->header('x-client-id', null);
        if (!$custodianKey) {
            return response()->json([
                'message' => 'you must provide your Custodian key',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $custodian = Custodian::where('client_id', $custodianKey)->first();
        if (! $custodian) {
            return response()->json([
                'message' => 'no known custodian matches the credentials provided',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // We could do the following with eloquent, but as it's quite a large hit,
        // it's far more performant to just pull the records manually and form
        // the resulting payload, to avoid Laravel bloat.
        $payload = [
            'user' => [
                'identity' => [],
            ],
            'registry' => [
                'training' => [],
                'history' => [],
            ],
        ];

        $registry = Registry::where('digi_ident', $input['ident'])->first();
        $payload['registry'] = $registry;

        $user = User::where('registry_id', $registry->id)->first()->setHidden(['email']);
        $payload['user'] = $user;

        $linkedTraining = RegistryHasTraining::where('registry_id', $registry->id)->select('training_id')->get()->toArray();
        $training = Training::whereIn('id', $linkedTraining)->get();
        $payload['registry']['training'] = $training;

        $identity = Identity::where('registry_id', $registry->id)->first();
        $payload['user']['identity'] = $identity;

        $rhh = DB::table('registry_has_histories')->where('registry_id', '=', $registry->id)->get();
        $historyResults = [];
        foreach ($rhh as $item) {
            $history = History::where('id', $item->history_id)->first()->toArray();

            $affiliation = Affiliation::where('id', $history['affiliation_id'])->first();
            $history['affiliation'] = $affiliation;

            // LS 18/02/25 - Removed for now as in talks by IG team - and not MVP
            // $endorsement = Endorsement::where('id', $history['endorsement_id'])->first();
            // $history['endorsement'] = $endorsement;

            // $infringement = Infringement::where('id', $history['infringement_id'])->first();
            // $history['infringement'] = $infringement;

            $project = Project::where('id', $history['project_id'])->first();
            $history['project'] = $project;

            $historyResults[] = $history;
        }
        $payload['registry']['history'] = $historyResults;

        $projectHasUserIds = ProjectHasUser::where('user_digital_ident', $registry->digi_ident)->pluck('id')->toArray();
        $linkedChPUs = CustodianHasProjectUser::where(['custodian_id' => $custodian->id])->whereIn('project_has_user_id', $projectHasUserIds)
            ->with(['projectHasUser.project' => function ($query) {
                $query->select(['id', 'title']);
            }])->get();
        $projectResults = [];
        foreach ($linkedChPUs as $chpu) {
            $projectResults[] = [
                'project_id' => $chpu->projectHasUser->project->id,
                'project_title' => $chpu->projectHasUser->project->title,
                'project_user_validation_status' => $chpu->modelState->state->name ?? null,
            ];
        }
        $payload['projects'] = $projectResults;

        if ($registry) {
            return response()->json([
                'message' => 'success',
                'data' => $payload,
            ], 200);
        }

        throw new NotFoundException();
    }
}
