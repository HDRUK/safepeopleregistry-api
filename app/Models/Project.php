<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\SearchManager;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;
    use SearchManager;

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
    ];

    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectHasUser::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ProjectHasCustodianApproval::class);
    }


    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organisation::class,
            'project_has_organisations'
        );
    }
}
