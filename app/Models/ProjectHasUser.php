<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ProjectHasUser",
 *     type="object",
 *     @OA\Property(property="project", ref="#/components/schemas/Project"),
 *     @OA\Property(property="role", ref="#/components/schemas/ProjectRole"),
 *     @OA\Property(property="affiliation", ref="#/components/schemas/Affiliation")
 * )
 *
 * @property int $project_id
 * @property string $user_digital_ident
 * @property int|null $project_role_id
 * @property int $primary_contact
 * @property int|null $affiliation_id
 * @property-read \App\Models\Affiliation|null $affiliation
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\Registry|null $registry
 * @property-read \App\Models\ProjectRole|null $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser whereAffiliationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser wherePrimaryContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser whereProjectRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasUser whereUserDigitalIdent($value)
 * @mixin \Eloquent
 */
class ProjectHasUser extends Model
{
    use HasFactory;

    public $incrementing = true;

    protected $table = 'project_has_users';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'user_digital_ident',
        'project_role_id',
        'primary_contact',
        'affiliation_id',
    ];

    public static function defaultValidationChecks(): array
    {
        return [
            [
                'name' => 'mandatory_training_complete',
                'description' => 'Mandatory training has been completed',
            ],
            [
                'name' => 'no_misconduct',
                'description' => 'The user has no record of misconduct',
            ],
            [
                'name' => 'no_relevant_criminal_record',
                'description' => 'The user has no relevant criminal record',
            ],
            [
                'name' => 'organisation_has_confirmed_the_user',
                'description' => 'The organisation has confirmed the user',
            ],
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ProjectRole>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class, 'project_role_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Registry>
     */
    public function registry(): BelongsTo
    {
        return $this->belongsTo(Registry::class, 'user_digital_ident', 'digi_ident');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Project>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Affiliation>
     */
    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id', 'id');
    }
}
