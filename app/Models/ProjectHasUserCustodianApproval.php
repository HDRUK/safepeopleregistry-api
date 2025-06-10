<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\StateWorkflow;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 *
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

        // When a new ProjectHasUserCustodianApproval record is being created,
        // automatically set its initial state.
        static::creating(function ($model) {
            if (in_array(StateWorkflow::class, class_uses($model))) {
                $model->setState(State::STATE_FORM_RECEIVED);
            }
        });
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ProjecHasUser>
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
