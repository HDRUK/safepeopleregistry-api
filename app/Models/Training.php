<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    /**
     * Whether or not this model supports timestamps
     * 
     * @var bool
     */
    public $timestamps = true;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'trainings';

    /**
     * What fields of this model are accepted as parameters
     * 
     * @var array
     */
    protected $fillable = [
        'registry_id',
        'provider',
        'awarded_at',
        'expires_at',
        'expires_in_years',
        'training_name',
    ];


}
