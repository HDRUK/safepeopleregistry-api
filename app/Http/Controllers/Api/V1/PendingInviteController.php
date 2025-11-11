<?php

namespace App\Http\Controllers\Api\V1;

use TriggerEmail;

use App\Models\User;
use App\Models\Affiliation;
use Illuminate\Http\Request;
use App\Models\PendingInvite;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\PendingInvites\CheckPendingInvite;

class PendingInviteController extends Controller
{
    use CommonFunctions;
    use Responses;

    /**
     * @OA\Get(
     *      path="/api/v1/pending_invites",
     *      summary="Return a list of pending invites",
     *      description="Return a list of pending invites",
     *      tags={"pending invites"},
     *      summary="PendingInvite@index",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *              @OA\Property(property="data",
     *                  ref="#/components/schemas/PendingInvite",
     *                  @OA\Property(property="charities", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="id", type="integer", example="1"),
     *                          @OA\Property(property="user_id", type="integer", example="1186569"),
     *                          @OA\Property(property="name", type="string", example="Health Pathways UK Charity"),
     *                          @OA\Property(property="organisation_id", type="integer", example="1"),
     *                          @OA\Property(property="status", type="string", example="3 WATERHOUSE SQUARE"),
     *                          @OA\Property(property="invite_accepted_at", type="string", format="date-time"),
     *                          @OA\Property(property="invite_sent_at", type="string", format="date-time"),
     *                          @OA\Property(property="invite_code", type="string", example="test"),
     *                      ),
     *                  ),
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
    public function index(Request $request)
    {
        $pendingInvites = PendingInvite::query()
            ->with([
                'user:id,name,user_group,unclaimed'
            ])
            ->paginate((int)$this->getSystemConfig('PER_PAGE'));

        return $this->OKResponse($pendingInvites);
    }
    
    //Hide from swagger docs
    public function resendInvite(CheckPendingInvite $request, int $inviteId)
    {
        if (!Gate::allows('admin')) {
            return $this->ForbiddenResponse();
        }

        $pendingInvite = PendingInvite::where([
            'id' => $inviteId,
            'status' => config('speedi.invite_status.PENDING'),
        ])->first();
        if (is_null($pendingInvite)) {
            return $this->NotFoundResponse();
        }

        $user = User::where([
            'id' => $pendingInvite->user_id,
            'unclaimed' => 1,
        ])->first();
        if (is_null($user)) {
            return $this->NotFoundResponse();
        }

        $email = [];

        switch ($user->user_group) {
            case User::GROUP_CUSTODIANS:
                $email = [
                    'type' => 'CUSTODIAN',
                    'to' => $user->custodian_id,
                    'unclaimed_user_id' => $user->id,
                    'by' => $user->id,
                    'identifier' => 'custodian_invite',
                    'inviteId' => $inviteId,
                ];

                break;
                
            case User::GROUP_USERS:
                $affiliation = Affiliation::where([
                    'registry_id' => $user->registry_id,
                ])->first();
            
                if (is_null($affiliation)) {
                    return $this->NotFoundResponse();
                }

                if ($user->is_delegate) {
                    $email = [
                        'type' => 'USER_DELEGATE',
                        'to' => $user->id,
                        'by' => $affiliation->organisation_id,
                        'identifier' => 'delegate_invite',
                        'inviteId' => $inviteId,
                    ];
                } else {
                    $email = [
                        'type' => 'USER',
                        'to' => $user->id,
                        'by' => $affiliation->organisation_id,
                        'identifier' => 'organisation_user_invite',
                        'inviteId' => $inviteId,
                    ];
                }

                break;

            case User::GROUP_ORGANISATIONS:
                $email = [
                    'type' => 'ORGANISATION',
                    'to' => $user->organisation_id,
                    'unclaimed_user_id' => $user->id,
                    'by' => $user->organisation_id,
                    'identifier' => 'organisation_invite',
                    'inviteId' => $inviteId,
                ];

                break;

            default:
                break;
        }

        if (!count($email)) {
            return $this->NotFoundResponse();
        }

        TriggerEmail::spawnEmail($email);

        return $this->OKResponse('Email invite resent');
    }
}
