<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;
use App\Http\Traits\Responses;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use App\Http\Requests\AuditLog\GetUserHistory;

/**
 * @OA\Tag(
 *     name="AuditLog",
 *     description="API endpoints for managing audit logs"
 * )
 */
class AuditLogController extends Controller
{
    use Responses;

    public function showUserHistory(GetUserHistory $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->NotFoundResponse();
        }

        $logs = Activity::query()
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('subject_type', get_class($user))
                        ->where('subject_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $q->where('causer_type', get_class($user))
                        ->where('causer_id', $user->id);
                });
            })
            ->with([
                'causer:id,first_name,last_name',
                'subject:id,first_name,last_name',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->OKResponse($logs);
    }

    public function showOrganisationHistory(Request $request, int $id): JsonResponse
    {
        $organisation = Organisation::find($id);

        if (!$organisation) {
            return $this->NotFoundResponse();
        }

        $logs = Activity::query()
            ->where(function ($query) use ($organisation) {
                $query->where(function ($q) use ($organisation) {
                    $q->where('subject_type', get_class($organisation))
                        ->where('subject_id', $organisation->id);
                })->orWhere(function ($q) use ($organisation) {
                    $q->where('causer_type', get_class($organisation))
                        ->where('causer_id', $organisation->id);
                });
            })
            ->with([
                'causer:id,first_name,last_name',
                'subject:id,organisation_name',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->OKResponse($logs);
    }
}
