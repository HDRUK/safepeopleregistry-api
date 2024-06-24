<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasIssuerApproval extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'organisation_has_issuer_approvals';

    /**
     * Whether or not this model supports timestamps
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * What fields of this model are accepted as parameters
     * 
     * @var array
     */
    protected $fillable = [
        'organisation_id',
        'issuer_id',
    ];
}
