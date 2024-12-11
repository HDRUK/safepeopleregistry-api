<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectHasCustodianApproval extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'project_has_custodian_approval';

    // Indicate if the model should have timestamp columns
    public $timestamps = true;

    // Specify the fillable attributes
    protected $fillable = [
        'project_id',
        'custodian_id',
    ];

    /**
     * Define the relationship with the Project model
     *
     * @return BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Define the relationship with the Custodian (or custodian) model
     *
     * @return BelongsTo
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id', 'id');
    }
}
