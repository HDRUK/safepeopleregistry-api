<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'affiliate_id',
    ];

    public function roles(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProjectRole::class, // Final model
            ProjectHasRole::class, // Intermediate model
            'project_id', // Foreign key on ProjectHasRole
            'id', // Foreign key on ProjectRole
            'id', // Local key on Project
            'project_role_id' // Local key on ProjectHasRole
        );
    }
    public function projectRoles(): HasMany
    {
        return $this->hasMany(ProjectHasRole::class);
    }

    public function organisation(): HasOne
    {
        return $this->hasOne(Organisation::class, 'id', 'affiliate_id');
    }


}
