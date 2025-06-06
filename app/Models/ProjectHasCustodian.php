<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $project_id
 * @property int $custodian_id
 * @property int $approved
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasCustodian whereProjectId($value)
 * @mixin \Eloquent
 */
class ProjectHasCustodian extends Model
{
    use HasFactory;

    protected $table = 'project_has_custodians';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'custodian_id',
        'approved',
    ];

    /**
     * Get the project that this relationship belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Project>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Get the custodian that this relationship belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id', 'id');
    }
}
