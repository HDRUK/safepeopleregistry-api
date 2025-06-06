<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @property int $custodian_id
 * @property int $validation_check_id
 * @property-read \App\Models\Custodian $custodian
 * @property-read \App\Models\ValidationCheck $validationCheck
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianHasValidationCheck whereValidationCheckId($value)
 * @mixin \Eloquent
 */
class CustodianHasValidationCheck extends Pivot
{
    protected $table = 'custodian_has_validation_check';

    public $timestamps = false;

    protected $fillable = [
        'custodian_id',
        'validation_check_id',
    ];

    /**
     * Get the custodian associated with this validation check.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class);
    }

    /**
     * Get the validation check associated with this custodian.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ValidationCheck>
     */
    public function validationCheck(): BelongsTo
    {
        return $this->belongsTo(ValidationCheck::class);
    }
}
