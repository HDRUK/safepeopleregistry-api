<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasCustodianPermission extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_custodian_permissions';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'permission_id',
        'custodian_id',
    ];
}
