<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistryHasOrganisation extends Model
{
    use HasFactory;

    protected $table = 'registry_has_organisations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'organisation_id',
    ];
}
