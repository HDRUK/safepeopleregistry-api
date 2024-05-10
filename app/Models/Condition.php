<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'conditions';
    
    /**
     * Whether or not this model suppors timestamps
     * 
     * @var bool
     */
    public $timestamps = true;

    /**
     * What fields of this model are accepted as parameters
     * 
     * @var array
     */
    protected $fillable = [
        'condition_field',
        'condition_value',
        'enabled',
    ];

    /**
     * Whether or not we have to ask Laravel to cast fields
     * 
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];
}
