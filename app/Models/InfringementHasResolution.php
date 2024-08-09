<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfringementHasResolution extends Model
{
    use HasFactory;

    public $table = 'infringement_has_resolutions';

    public $timestamps = false;

    protected $fillable = [
        'infringement_id',
        'resolution_id',
    ];
}
