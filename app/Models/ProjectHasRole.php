<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="ProjectHasRole",
 *     type="object",
 *     title="ProjectHasRole",
 *     description="Pivot model representing the relationship between projects and roles",
 *     @OA\Property(
 *         property="project_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the project"
 *     ),
 *     @OA\Property(
 *         property="project_role_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the project role"
 *     )
 * )
 * 
 * @property int $project_id
 * @property int $project_role_id
 * @property-read \App\Models\ProjectRole|null $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasRole query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasRole whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasRole whereProjectRoleId($value)
 * @mixin \Eloquent
 */
class ProjectHasRole extends Model
{
    use HasFactory;

    protected $table = 'project_has_project_roles';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'project_role_id',
    ];

    /**
     * Get a random user (example method).
     *
     * @return \App\Models\User|null
     */
    public function randomUser()
    {
        return User::inRandomOrder()->first();
    }

    /**
     * Get the role associated with this project-role relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ProjectRole>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class, 'project_role_id', 'id');
    }
}
