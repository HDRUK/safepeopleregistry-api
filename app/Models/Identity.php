<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int $registry_id
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $town
 * @property string|null $county
 * @property string|null $country
 * @property string|null $postcode
 * @property string|null $dob
 * @property string|null $idvt_completed_at
 * @property string|null $idvt_result_text
 * @property string|null $idvt_context
 * @property int $idvt_success
 * @property string|null $idvt_identification_number
 * @property string|null $idvt_document_type
 * @property string|null $idvt_document_number
 * @property string|null $idvt_document_country
 * @property string|null $idvt_document_valid_until
 * @property string|null $idvt_attempt_id
 * @property string|null $idvt_context_id
 * @property string|null $idvt_document_dob
 * @property string|null $idvt_started_at
 * @method static \Database\Factories\IdentityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtAttemptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtContext($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtContextId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtDocumentValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtIdentificationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtResultText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereIdvtSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Identity withoutTrashed()
 * @mixin \Eloquent
 */
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
