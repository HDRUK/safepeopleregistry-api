<?php

namespace App\Models;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectHasSponsorship extends Model
{
    protected $table = 'project_has_sponsorships';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'sponsor_id',
    ];

    /**
     * Get the organisation associated with the approval.
     */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'sponsor_id');
    }

    /**
     * Get the project for this sponsorship
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
