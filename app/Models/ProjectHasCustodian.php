<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHasCustodian extends Model
{
    use HasFactory;

    protected $table = 'project_has_custodians';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'custodian_id',
        'approved',
    ];

    /**
     * Get the project that this relationship belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Get the custodian that this relationship belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id', 'id');
    }
}
