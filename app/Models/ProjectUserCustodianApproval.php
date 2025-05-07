<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUserCustodianApproval extends Model
{
    protected $table = 'project_user_has_custodian_approval';

    protected $fillable = [
        'project_user_id',
        'custodian_id',
    ];

    public function projectUser(): BelongsTo
    {
        return $this->belongsTo(ProjectHasUser::class, 'project_user_id');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class);
    }
}
