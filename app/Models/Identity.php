<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'identities';

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
        'registry_id',
        'selfie_path',
        'passport_path',
        'drivers_license_path',
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
        'dob',
        'idvt_result',
        'idvt_result_perc',
        'idvt_errors',
        'idvt_completed_at',
    ];

    /**
     * Whether or not we want certain fields hidden from the payload
     * 
     * @var array
     */
    protected $hidden = [
        'selfie_path',
        'passport_path',
        'drivers_license_path',
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
        'dob',
    ];
}
