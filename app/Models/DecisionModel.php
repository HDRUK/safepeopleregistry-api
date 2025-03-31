<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasOne;

class DecisionModel extends Model
{
    use HasFactory;

    protected $table = 'decision_models';

    public $timestamps = true;

    protected $fillable = [
        'model_type',
        'conditions',
        'rule_class',
    ];

    public function custodianModelConfig(): HasOne
    {
        return $this->hasOne(CustodianModelConfig::class,
            'entity_model_id');
    }
}
