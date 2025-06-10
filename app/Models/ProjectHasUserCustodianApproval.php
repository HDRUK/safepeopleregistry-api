<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $project_id
 * @property int $user_id
 * @property int $custodian_id
 * @property int $approved
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Custodian $custodian
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectUserCustodianApproval whereUserId($value)
 * @mixin \Eloquent
 */
class ProjectHasUserCustodianApproval extends Model
{
    protected $table = 'project_has_user_custodian_approval';

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'project_has_user_id',
        'custodian_id',
        'approved',
        'comment',
    ];

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
}
