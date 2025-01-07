<?php

namespace App\Models;

use App\Observers\ProjectHasUserObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ProjectHasUserObserver::class])]
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
            ProjectHasCustodian::class,
            Project::class,
            'id',
            'id',
            'project_id',
            'project_id'
        )->where('approved', true);
    }
}
