<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Traits\SearchManager;
use App\Traits\StateWorkflow;
use App\Traits\FilterManager;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    use SearchManager;
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
