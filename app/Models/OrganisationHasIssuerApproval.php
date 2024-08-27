<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasIssuerApproval extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_issuer_approvals';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'issuer_id',
    ];
}
