<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Traits\SearchManager;
use App\Traits\SearchProject;
use App\Traits\StateWorkflow;
use App\Traits\FilterManager;

/**
 * @OA\Schema(
 *      schema="Project",
 *      title="Project",
 *      description="Project model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example=1,
 *          description="Model primary key"
 *      ),
 *      @OA\Property(property="title",
 *          type="string",
 *          example="Project name"
 *      ),
 *      @OA\Property(property="unique_id",
 *          type="string",
 *          example="89AItHDuaqXsfgqOA85d"
 *      ),
 *      @OA\Property(property="lay_summary",
 *          type="string",
 *          example="This study aims to evaluate how digital mental health interventions (such as mobile apps for meditation, cognitive behavioral therapy, and mental health tracking) affect the mental health and well-being of young adults aged 18-30."
 *      ),
 *      @OA\Property(property="public_benefit",
 *          type="string",
 *          example="The findings from this research could lead to improved digital health interventions tailored to the mental health needs of young adults.",
 *          description="A unique identifier for Custodian's within SOURSD"
 *      ),
 *      @OA\Property(property="request_category_type",
 *          type="string",
 *          example="Health and Social Research"
 *      ),
 *      @OA\Property(property="technical_summary",
 *          type="string",
 *          example="This project involves analyzing anonymized, aggregated data from digital health applications used by young adults."
 *      ),
 *      @OA\Property(property="other_approval_commitees",
 *          type="string",
 *          example="This project requires approval from:  University Institutional Review Board (IRB) to ensure ethical considerations are met. Data Access Committee (DAC) from the app providers to secure permissions for using anonymized, aggregated data."
 *      ),
 *      @OA\Property(property="start_date",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="end_date",
 *          type="bool",
 *          example="false"
 *      )
 * )
 */
class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    use SearchManager;
    use SearchProject;
    use StateWorkflow;
    use FilterManager;

    protected $table = 'projects';

    public $timestamps = true;

    protected $fillable = [
        'unique_id',
        'title',
        'lay_summary',
        'public_benefit',
        'request_category_type',
        'technical_summary',
        'other_approval_committees',
        'start_date',
        'end_date',
    ];

    /**
     * Compiles a list of exposed searchable fields
     */
    protected static array $searchableColumns = [
        'title',
        'start_date',
        'end_date',
        'unique_id',
        'status',
    ];

    protected static array $sortableColumns = [
        'title',
    ];

    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectHasUser::class);
    }

    public function projectDetail()
    {
        return $this->hasOne(ProjectDetail::class);
    }

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organisation::class,
            'project_has_organisations'
        );
    }

    public function custodians(): BelongsToMany
    {
        return $this->belongsToMany(
            Custodian::class,
            'project_has_custodians',
            'project_id',
            'custodian_id'
        )->withPivot('approved');
    }

    public function approvals(): BelongsToMany
    {
        return $this->belongsToMany(
            Custodian::class,
            'project_has_custodians',
            'project_id',
            'custodian_id'
        )->wherePivot('approved', true);
    }

    public function modelState(): MorphOne
    {
        return $this->morphOne(ModelState::class, 'stateable');
    }
}
