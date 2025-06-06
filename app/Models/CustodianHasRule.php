<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $custodian_id
 * @property int $rule_id
 * @property-read \App\Models\Custodian $custodian
 * @property-read \App\Models\Rules $rule
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasRule whereRuleId($value)
 * @mixin \Eloquent
 */
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
     *
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id');
    }

    /**
     * Get the rule associated with this record.
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Rules>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rules::class, 'rule_id');
    }
}
