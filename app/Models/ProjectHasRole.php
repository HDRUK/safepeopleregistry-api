<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectHasRole extends Model
{
    use HasFactory;

    protected $table = 'project_has_project_roles';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'project_role_id',
    ];
    public function randomUser()
    {
        return User::inRandomOrder()->first();
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ProjectRole>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(ProjectRole::class, 'project_role_id', 'id');
    }
}
