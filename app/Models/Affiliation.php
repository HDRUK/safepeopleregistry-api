<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'affiliations';

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
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
        'delegate',
        'verified',
    ];

    /**
     * Whether or not we want certain fields hidden from the payload
     * 
     * @var array
     */
    protected $hidden = [
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
        'delegate',
        'verified',
    ];
}
