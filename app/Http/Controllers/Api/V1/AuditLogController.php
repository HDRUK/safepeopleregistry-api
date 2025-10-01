<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Affiliation;
use App\Models\Organisation;
use Illuminate\Http\Request;
use App\Models\ValidationLog;
use App\Http\Traits\Responses;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use App\Http\Resources\ActivityResource;
use App\Http\Requests\AuditLog\GetUserHistory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @OA\Tag(
 *     name="AuditLog",
 *     description="API endpoints for managing audit logs"
 * )
 */
class AuditLogController extends Controller
{
    use Responses;
    protected const ALLOWED_TYPES = [
        User::class,
        Organisation::class,
        ValidationLog::class,
        Affiliation::class,
    ];

    public function showUserHistory(GetUserHistory $request, int $id)
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
            ->whereHasMorph('subject', self::ALLOWED_TYPES)
            ->where(function ($query) {
                $query->whereNull('causer_type')
                    ->orWhereHasMorph('causer', self::ALLOWED_TYPES);
            })
            ->with([
                'causer' => function (Relation $relation) {
                    if ($relation instanceof MorphTo) {
                        $relation->constrain([
                            User::class         => fn ($q) => $q->select('id','first_name','last_name'),
                            Organisation::class => fn ($q) => $q->select('id','organisation_name'),
                            ValidationLog::class=> fn ($q) => $q->select('id'),
                            Affiliation::class  => fn ($q) => $q
                                ->select(['id','registry_id'])
                                ->with([
                                    'registry:id',
                                    'registry.user:id,first_name,last_name,registry_id,evaluation',
                                ]),
                        ])->morphWith([
                            Affiliation::class => ['registry.user'],
                        ]);
                    }
                },
                'subject' => function (Relation $relation) {
                    if ($relation instanceof MorphTo) {
                        $relation->constrain([
                            User::class         => fn ($q) => $q->select('id','first_name','last_name'),
                            Organisation::class => fn ($q) => $q->select('id','organisation_name'),
                            ValidationLog::class=> fn ($q) => $q->select('id'),
                            Affiliation::class  => fn ($q) => $q
                                ->select(['id','registry_id'])
                                ->with([
                                    'registry:id',
                                    'registry.user:id,first_name,last_name,registry_id,evaluation',
                                ]),
                        ])->morphWith([
                            Affiliation::class => ['registry.user'],
                        ]);
                    }
                },
            ])
            ->latest('created_at')
            ->get();

        // return $this->OKResponse($logs);
        // trim the output
        return ActivityResource::collection($logs);
    }

    public function showOrganisationHistory(Request $request, int $id)
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
            ->whereHasMorph('subject', self::ALLOWED_TYPES)
            ->where(function ($query) {
                $query->whereNull('causer_type')
                    ->orWhereHasMorph('causer', self::ALLOWED_TYPES);
            })
            ->with([
                'causer' => function (Relation $relation) {
                    if ($relation instanceof MorphTo) {
                        $relation->constrain([
                            User::class         => fn ($q) => $q->select('id','first_name','last_name'),
                            Organisation::class => fn ($q) => $q->select('id','organisation_name'),
                            ValidationLog::class=> fn ($q) => $q->select('id'),
                            Affiliation::class  => fn ($q) => $q
                                ->select(['id','registry_id'])
                                ->with([
                                    'registry:id',
                                    'registry.user:id,first_name,last_name,registry_id',
                                ]),
                        ])->morphWith([
                            Affiliation::class => ['registry.user'],
                        ]);
                    }
                },
                'subject' => function (Relation $relation) {
                    if ($relation instanceof MorphTo) {
                        $relation->constrain([
                            User::class         => fn ($q) => $q->select('id','first_name','last_name'),
                            Organisation::class => fn ($q) => $q->select('id','organisation_name'),
                            ValidationLog::class=> fn ($q) => $q->select('id'),
                            Affiliation::class  => fn ($q) => $q
                                ->select(['id','registry_id'])
                                ->with([
                                    'registry:id',
                                    'registry.user:id,first_name,last_name,registry_id',
                                ]),
                        ])->morphWith([
                            Affiliation::class => ['registry.user'],
                        ]);
                    }
                },
            ])
            ->latest('created_at')
            ->get();

        // return $this->OKResponse($logs);
        // trim the output
        return ActivityResource::collection($logs);
    }
}
