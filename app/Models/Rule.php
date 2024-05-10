<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rule extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'rules';

    /**
     * Whether or not this model supports timestamps
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
        'name',
        'fn',
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

    public function conditions(): BelongsToMany
    {
        $this->hasMany(Condition::class, 'rule_has_conditions');
    }
}
