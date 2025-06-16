<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use App\Models\Custodian;
use App\Models\CustodianHasProjectOrganisation;
use Illuminate\Support\Facades\Gate;
use App\Traits\CommonFunctions;

class CustodianHasProjectOrganisationController extends Controller
{
    use Responses;
    use CommonFunctions;


    public function index(Request $request, int $custodianId)
    {
        try {
            $custodian = Custodian::findOrFail($custodianId);

            if (!Gate::allows('view', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $searchName = $request->input('name');
            $perPage = $request->integer('per_page', (int)$this->getSystemConfig('PER_PAGE'));

            $projectId = $request->input('project_id');

            $records = CustodianHasProjectOrganisation::with([
                'modelState.state',
                'projectOrganisation.organisation'
            ])
                ->where('custodian_id', $custodianId)
                ->when(!empty($searchName), function ($query) use ($searchName) {
                    $query->where(function ($subQuery) use ($searchName) {
                        $subQuery->orWhereHas('organisation', function ($q) use ($searchName) {
                            /** @phpstan-ignore-next-line */
                            $q->searchViaRequest(['name' => $searchName]);
                        });
                    });
                })
                ->when(!empty($projectId), function ($query) use ($projectId) {
                    $query->whereHas('projectHasUser.project', function ($q) use ($projectId) {
                        $q->where('id', $projectId);
                    });
                })
                ->paginate($perPage);

            return $this->OKResponse($records);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    public function show(
        Request $request,
        int $custodianId,
        int $organisationId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('view', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $puhca = CustodianHasProjectOrganisation::with([
                'modelState.state',
                'organisation'
            ])
                ->where([
                    'organisation_id' => $organisationId,
                    'custodian_id' => $custodianId
                ])->first();

            return $this->OKResponse($puhca);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }



    public function update(
        Request $request,
        int $custodianId,
        int $organisationId,
    ) {
        try {
            $custodian = Custodian::findOrFail($custodianId);
            if (!Gate::allows('update', $custodian)) {
                return $this->ForbiddenResponse();
            }

            $cho = CustodianHasProjectOrganisation::where([
                'organisation_id' => $organisationId,
                'custodian_id' => $custodianId,
            ])->first();

            $status = $request->get('status');

            if (isset($status)) {
                $originalStatus = $cho->getState();
                if ($cho->canTransitionTo($status)) {
                    $cho->transitionTo($status);
                } else {
                    return $this->ErrorResponse('cannot transition to state = ' . $status);
                }

                $comment = $request->get('comment');
                if (isset($comment)) {
                    $log = 'Approval status change to ' . $status . ' from ' . $originalStatus . ' with comment:' . $comment;
                    /*$userId = $cho->projectHasUser->registry->user->id;
                    UserAuditLog::create([
                        'user_id' => $userId,
                        'class'   => CustodianHasProjectUser::class,
                        'log'     => $log,
                    ]);*/
                };
            }

            return $this->OKResponse($cho);
        } catch (Exception $e) {
            return $this->ErrorResponse($e->getMessage());
        }
    }

    public function getWorkflowStates(Request $request)
    {
        $model = new CustodianHasProjectOrganisation();
        return $this->OKResponse($model->getAllStates());
    }
}
