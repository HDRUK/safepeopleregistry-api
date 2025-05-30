<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([UserObserver::class])]
class CustodianHasRule extends Pivot
{
    protected $table = 'custodian_has_rules';

    protected $fillable = [
        'custodian_id',
        'rule_id'
    ];

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Get the custodian associated with this record.
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id');
    }

    /**
     * Get the rule associated with this record.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rules::class, 'rule_id');
    }
}
