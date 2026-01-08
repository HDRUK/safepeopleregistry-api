<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecisionModelLog extends Model
{
    protected $table = 'decision_model_logs';

    public $timestamps = false;

    protected $fillable = [
        'decision_model_id',
        'custodian_id',
        'subject_id',
        'model_type',
        'status',
    ];
}
