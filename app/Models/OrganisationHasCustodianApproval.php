<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasCustodianApproval extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_custodian_approvals';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'custodian_id',
    ];
}
