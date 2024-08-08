<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasIssuerPermission extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_issuer_permissions';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'permission_id',
        'issuer_id',
    ];
}
