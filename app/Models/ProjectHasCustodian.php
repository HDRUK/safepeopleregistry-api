<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Observers\ProjectHasCustodianObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ProjectHasCustodianObserver::class])]
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
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Get the custodian that this relationship belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function custodian()
    {
        return $this->belongsTo(Custodian::class, 'custodian_id', 'id');
    }
}
