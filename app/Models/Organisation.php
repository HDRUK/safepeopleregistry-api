<?php

namespace App\Models;

use App\Models\Permission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organisation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'organisations';

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
        'organisation_name',
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
        'lead_applicant_organisation_name',
        'lead_applicant_email',
        'password',
        'organisation_unique_id',
        'applicant_names',
        'funders_and_sponsors',
        'sub_license_arrangements',
        'verified',
        'dsptk_ods_code',
        'iso_27001_certified',
        'ce_certified',
        'ce_certification_num',
    ];

    /**
     * Whether or not we have to ask Laravel to cast fields
     * 
     * @var array
     */
    protected $casts = [
        'verified' => 'boolean',
        'iso_27001_certified' => 'boolean',
        'ce_certified' => 'boolean',
    ];

    /**
     * Whether or not we want certain fields hidden from the payload
     * 
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'organisation_has_issuer_permissions',
        );
    }

    public function approvals(): BelongsToMany
    {
        return $this->belongsToMany(
            Issuer::class,
            'organisation_has_issuer_approvals',
        );
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(
            File::class,
            'organisation_has_files',
        );
    }

    public function registries(): BelongsToMany
    {
        return $this->belongsToMany(
            Registry::class,
            'registry_has_organisations',
        );
    }
}
