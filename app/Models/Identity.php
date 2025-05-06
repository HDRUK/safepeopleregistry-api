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
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
        'dob',
        'idvt_success',
        'idvt_identification_number',
        'idvt_document_type',
        'idvt_document_number',
        'idvt_document_country',
        'idvt_document_valid_until',
        'idvt_attempt_id',
        'idvt_context_id',
        'idvt_document_dob',
        'idvt_context',
        'idvt_completed_at',
        'idvt_result_text',
        'idvt_started_at',
    ];

    // protected $hidden = [
    //     'selfie_path',
    //     'passport_path',
    //     'drivers_license_path',
    //     'address_1',
    //     'address_2',
    //     'town',
    //     'county',
    //     'country',
    //     'postcode',
    //     'dob',
    // ];
}
