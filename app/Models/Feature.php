<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'features';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'scope',
        'value',
        'description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'value' => 'boolean',
    ];
}
