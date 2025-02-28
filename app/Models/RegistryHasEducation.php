<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistryHasEducation extends Model
{
    use HasFactory;

    public $table = 'registry_has_educations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'education_id',
    ];
}
