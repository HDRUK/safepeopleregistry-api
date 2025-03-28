<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
