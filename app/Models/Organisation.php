<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\SearchManager;

class Organisation extends Model
{
    use HasFactory;
    use SearchManager;

    protected $table = 'organisations';

    public $timestamps = true;

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
        'dsptk_certified',
        'iso_27001_certified',
        'iso_27001_certification_num',
        'ce_certified',
        'ce_certification_num',
        'idvt_result',
        'idvt_result_perc',
        'idvt_errors',
        'idvt_completed_at',
        'companies_house_no',
        'sector_id',
        'charity_registration_id',
        'ror_id',
        'website',
        'smb_status',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'iso_27001_certified' => 'boolean',
        'ce_certified' => 'boolean',
        'idvt_result' => 'boolean',
    ];

    protected static array $searchableColumns = [
        'organisation_name',
    ];

    protected static array $sortableColumns = [
        'organisation_name',
    ];

    protected $hidden = [
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'organisation_has_custodian_permissions',
        );
    }

    public function approvals(): BelongsToMany
    {
        return $this->belongsToMany(
            Custodian::class,
            'organisation_has_custodian_approvals',
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

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
