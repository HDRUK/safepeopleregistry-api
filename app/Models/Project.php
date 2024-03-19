<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'projects';

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
        'registry_id',
        'name',
        'public_benefit',
        'runs_to',
        'affiliate_id',
    ];
}
