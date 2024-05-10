<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleHasCondition extends Model
{
    use HasFactory;

    /**
     * The table associated with this model
     * 
     * @var string
     */
    protected $table = 'rule_has_conditions';

    /**
     * Whether or not this model supports timestamps
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * What fields of this model are accepted as parameters
     * 
     * @var array
     */
    protected $fillable = [
        'rule_id',
        'condition_id',
    ];
}
