<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $organisation_id
 * @property int $custodian_id
 * @property int $approved
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon $created_at
 * @property-read \App\Models\Custodian|null $custodian
 * @property-read \App\Models\Organisation|null $organisation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianApproval whereOrganisationId($value)
 * @mixin \Eloquent
 */
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Organisation>
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    /**
     * Get the custodian associated with the approval.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id');
    }
}
