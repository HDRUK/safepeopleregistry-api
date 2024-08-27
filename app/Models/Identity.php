<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Identity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'identities';

    public $timestamps = true;

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
