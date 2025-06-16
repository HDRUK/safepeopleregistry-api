<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $project_id
 * @property int $organisation_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProjectHasOrganisation whereProjectId($value)
 * @mixin \Eloquent
 */
class ProjectHasOrganisation extends Model
{
    use HasFactory;

    protected $table = 'project_has_organisations';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'organisation_id',
    ];


    /**
     * Get the organisation associated with the approval.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Organisation>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }
}
