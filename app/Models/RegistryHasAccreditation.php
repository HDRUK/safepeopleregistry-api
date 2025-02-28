<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistryHasAccreditation extends Model
{
    use HasFactory;

    public $table = 'registry_has_accreditations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'accreditation_id',
    ];
}
