<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistryHasEmployment extends Model
{
    use HasFactory;

    public $table = 'registry_has_employments';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'employment_id',
    ];
}
