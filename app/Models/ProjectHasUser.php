<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHasUser extends Model
{
    use HasFactory;

    protected $table = 'project_has_users';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'user_digital_ident',
        'project_role_id',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class, 'project_role_id', 'id');
    }

    public function registry(): BelongsTo
    {
        return $this->belongsTo(Registry::class, 'user_digital_ident', 'digi_ident');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }


    public function approvals(): HasManyThrough
    {
        return $this->hasManyThrough(
            Project::class,
            ProjectHasCustodianApproval::class,
            'project_id',   // Foreign key on ProjectHasCustodianApproval
            'id',           // Foreign key on Project
            'project_id',   // Local key on ProjectHasUser
            'project_id'    // Local key on ProjectHasCustodianApproval
        );
    }
}
