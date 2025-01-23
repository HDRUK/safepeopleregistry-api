<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    use HasFactory;

    public $table = 'affiliations';

    public $timestamps = true;

    protected $fillable = [
        'organisation_id',
        'current_employer',
        'member_id',
        'relationship'
    ];

    /**
     * Get the organisation related to the affiliation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'id');
    }

    /**
     * Get the organisation related to the affiliation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registryHasAffiliations()
    {
        return $this->hasMany(RegistryHasAffiliation::class, 'affiliation_id');
    }

}
