<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\StateWorkflow;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/*
 * @OA\Schema(
 *     schema="ProjectHasUserCustodianApproval",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="project_has_user_id", type="integer", example=1),
 *     @OA\Property(property="custodian_id", type="integer", example=1),
 *     @OA\Property(property="approved", type="boolean", example=true),
 *     @OA\Property(property="comment", type="string", example="Approval comment"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="projectHasUser", ref="#/components/schemas/ProjectHasUser"),
 * )
 *
 * @property int $project_has_user_id
 * @property int $custodian_id
 * @property int $approved
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Custodian $custodian
 * @property-read \App\Models\ProjectHasUser $projectHasUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUserCustodianApproval whereUserId($value)
 * @mixin \Eloquent
 */

class ProjectHasUserCustodianApproval extends Model
{
    use StateWorkflow;
    protected $table = 'project_has_user_custodian_approval';

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'project_has_user_id',
        'custodian_id',
        'approved',
        'comment',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            if (in_array(StateWorkflow::class, class_uses($model))) {
                $model->setState(State::STATE_FORM_RECEIVED);
                $model->save();
            }
        });
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ProjectHasUser>
     */
    public function projectHasUser(): BelongsTo
    {
        return $this->belongsTo(ProjectHasUser::class, 'project_has_user_id');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id');
    }

    public function modelState(): MorphOne
    {
        return $this->morphOne(ModelState::class, 'stateable');
    }
}
