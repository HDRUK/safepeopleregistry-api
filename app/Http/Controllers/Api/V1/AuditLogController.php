<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Affiliation;
use App\Models\Organisation;
use App\Models\ValidationLog;
use App\Models\CustodianHasProjectUser;
use App\Http\Traits\Responses;
use App\Traits\CommonFunctions;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use App\Http\Resources\ActivityResource;
use App\Http\Requests\AuditLog\GetUserHistory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Http\Requests\AuditLog\GetOrganisationHistory;

/**
 * @OA\Tag(
 *     name="AuditLog",
 *     description="API endpoints for managing audit logs"
 * )
 */
class AuditLogController extends Controller
{
    use CommonFunctions;
    use Responses;
    protected const ALLOWED_TYPES = [
        User::class,
        Organisation::class,
        ValidationLog::class,
        Affiliation::class,
    ];

    /**
     * Subjects that may only appear in a user's history, not an Organisation's.
     */
    protected const USER_HISTORY_SUBJECT_TYPES = [
        ...self::ALLOWED_TYPES,
        CustodianHasProjectUser::class,
    ];

    public function showUserHistory(GetUserHistory $request, int $id)
    {
        $loggedInUserId = $request->user()?->id;
        $loggedInUser = User::where('id', $loggedInUserId)->first();

        $perPage = $request->integer('per_page', (int)$this->getSystemConfig('PER_PAGE'));

        $user = User::find($id);

        if (!$user) {
            return $this->NotFoundResponse();
        }

        $userIdsInThisCustodian = [];

        if ($loggedInUser->custodian_user_id !== null) {
            $custodianId = $loggedInUser->custodian_user?->custodian_id;
            $userIdsInThisCustodian = User::whereHas(
                'custodian_user',
                fn ($q) =>
                $q->where('custodian_id', $custodianId)
            )->pluck('id')->toArray();
        }

        // A Custodian confirming they revoked the data access of a leaver is part of the leaver's history.
        $custodianProjectUserIds = CustodianHasProjectUser::whereHas(
            'projectHasUser.registry',
            fn ($q) => $q->whereKey($user->registry_id)
        )->pluck('id')->toArray();

        // This will show activity associated to the given user, caused either by the user themselves or by other
        // users in the same custodian group as the logged-in user. It will also show activity caused by the user on other subjects,
        // and any Custodian's confirmation that the user's data access was revoked.
        // It also masks email addresses in the properties of the activity log
        $logs = Activity::query()
            ->where(function ($query) use ($user, $userIdsInThisCustodian, $custodianProjectUserIds) {
                $query->where(function ($q) use ($user, $userIdsInThisCustodian) {
                    $q->where([
                            'subject_type' => get_class($user),
                            'subject_id' => $user->id,
                            'causer_type' => get_class($user),
                        ])
                        ->whereIn('causer_id', $userIdsInThisCustodian);
                })
                ->orWhere(function ($q) use ($user) {
                    $q->where([
                        'causer_type' => get_class($user),
                        'causer_id' => $user->id
                    ]);
                })
                ->orWhere(function ($q) use ($custodianProjectUserIds) {
                    $q->where('subject_type', CustodianHasProjectUser::class)
                        ->whereIn('subject_id', $custodianProjectUserIds)
                        ->where('event', 'access_revoked');
                });
            })
            ->whereHasMorph('subject', self::USER_HISTORY_SUBJECT_TYPES)
            ->where(function ($query) {
                $query->whereNull('causer_type')
                    ->orWhereHasMorph('causer', self::ALLOWED_TYPES);
            })
            ->with([
                'causer' => function (Relation $relation) {
                    if ($relation instanceof MorphTo) {
                        $relation->constrain([
                            User::class         => fn ($q) => $q->select('id', 'first_name', 'last_name'),
                            Organisation::class => fn ($q) => $q->select('id', 'organisation_name'),
                            ValidationLog::class => fn ($q) => $q->select('id'),
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
                            User::class         => fn ($q) => $q->select('id', 'first_name', 'last_name'),
                            Organisation::class => fn ($q) => $q->select('id', 'organisation_name'),
                            ValidationLog::class => fn ($q) => $q->select('id'),
                            Affiliation::class  => fn ($q) => $q
                                ->select(['id','registry_id'])
                                ->with([
                                    'registry:id',
                                    'registry.user:id,first_name,last_name,registry_id',
                                ]),
                        ])->morphWith([
                            Affiliation::class => ['registry.user'],
                            CustodianHasProjectUser::class => ['projectHasUser.registry.user', 'projectHasUser.project:id,title'],
                        ]);
                    }
                },
            ])
            ->latest('created_at')
            ->paginate($perPage)
            ->through(function ($activityLog) use ($loggedInUser) {
                if (
                    in_array($loggedInUser->user_group, [User::GROUP_ORGANISATIONS, User::GROUP_CUSTODIANS]) &&
                    $activityLog->causer_id !== null &&
                    $activityLog->causer_id !== $loggedInUser->id
                ) {
                    $activityLog->description = '';
                }

                $old = $activityLog->properties['old'] ?? [];
                if (isset($old['email'])) {
                    $old['email'] = '***';
                }
                $attributes = $activityLog->properties['attributes'] ?? [];
                if (isset($attributes['email'])) {
                    $attributes['email'] = '***';
                }
                $properties = ['old' => $old, 'attributes' => $attributes];
                if ($activityLog->subject instanceof CustodianHasProjectUser) {
                    $properties['project_title'] = $activityLog->subject->projectHasUser->project?->title;
                }
                $activityLog->properties = collect($properties);

                return $activityLog;
            });

        // return $this->OKResponse($logs);
        // trim the output
        return ActivityResource::collection($logs);
    }

    public function showOrganisationHistory(GetOrganisationHistory $request, int $id)
    {
        $loggedInUserId = $request->user()?->id;
        $perPage = $request->integer('per_page', (int)$this->getSystemConfig('PER_PAGE'));

        $loggedInUser = User::where('id', $loggedInUserId)->first();
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
                            User::class         => fn ($q) => $q->select('id', 'first_name', 'last_name'),
                            Organisation::class => fn ($q) => $q->select('id', 'organisation_name'),
                            ValidationLog::class => fn ($q) => $q->select('id'),
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
                            User::class         => fn ($q) => $q->select('id', 'first_name', 'last_name'),
                            Organisation::class => fn ($q) => $q->select('id', 'organisation_name'),
                            ValidationLog::class => fn ($q) => $q->select('id'),
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
            ->paginate($perPage);

        // return $this->OKResponse($logs);
        // trim the output
        return ActivityResource::collection($logs);
    }
}
