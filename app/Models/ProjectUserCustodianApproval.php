<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectUserCustodianApproval extends Model
{
    protected $table = 'project_user_has_custodian_approval';

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'project_id',
        'user_id',
        'custodian_id',
        'approved',
        'comment'
    ];

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Project>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id');
    }
}
