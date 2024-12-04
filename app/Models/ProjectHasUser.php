<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
}
