<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issuer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'issuers';

    /**
     * Whether or not this model supports timestamps
     * 
     * @var bool
     */
    public $timestamps = true;

    /**
     * What fields of this model are accepted as parameters
     * 
     * @var array
     */
    protected $fillable = [
        'name',
        'unique_identifier',
        'calculated_hash',
        'contact_email',
        'enabled',
        'invite_accepted_at',
        'invite_sent_at',
        'idvt_required',
    ];

    /**
     * Whether or not we have to ask Laravel to cast fields
     * 
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
        'idvt_required' => 'boolean',
    ];

        /**
     * Whether or not we want certain fields hidden from the payload
     * 
     * @var array
     */
    protected $hidden = [
        'unique_identifier',
        'calculated_hash',
    ];
}
