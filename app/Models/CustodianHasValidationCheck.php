<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
