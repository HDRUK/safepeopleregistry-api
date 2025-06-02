<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\OrganisationHasCustodianApprovalObserver;

class OrganisationHasCustodianApproval extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_custodian_approvals';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'custodian_id',
        'approved',
        'comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the organisation associated with the approval.
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    /**
     * Get the custodian associated with the approval.
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id');
    }
}
