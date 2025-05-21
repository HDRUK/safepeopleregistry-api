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

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class);
    }

    public function validationCheck(): BelongsTo
    {
        return $this->belongsTo(ValidationCheck::class);
    }
}
