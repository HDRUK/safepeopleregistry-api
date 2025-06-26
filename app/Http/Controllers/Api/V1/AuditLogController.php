<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\Responses;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

/**
 * @OA\Tag(
 *     name="AuditLog",
 *     description="API endpoints for managing audit logs"
 * )
 */
class AuditLogController extends Controller
{
    use Responses;

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}/audit-logs",
     *     tags={"AuditLog"},
     *     summary="Get audit logs for a specific user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Activity")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function showUserHistory(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return $this->NotFoundResponse('User not found');
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
}
