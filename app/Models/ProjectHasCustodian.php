<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ProjectHasCustodian",
 *     type="object",
 *     title="ProjectHasCustodian",
 *     description="Pivot model representing the relationship between projects and custodians",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the project-custodian relationship"
 *     ),
 *     @OA\Property(
 *         property="project_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the project"
 *     ),
 *     @OA\Property(
 *         property="custodian_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the custodian"
 *     ),
 *     @OA\Property(
 *         property="approved",
 *         type="integer",
 *         example=1,
 *         description="Indicates whether the custodian is approved for the project (1 for approved, 0 for not approved)"
 *     )
 * )
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
