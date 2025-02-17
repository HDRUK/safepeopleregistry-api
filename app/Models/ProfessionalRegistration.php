<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfessionalRegistration extends Model
{
    use HasFactory;

    public $table = 'professional_registrations';

    public $timestamps = true;

    protected $fillable = [
        'member_id',
        'name',
    ];

    /**
     * Get the organisation related to the affiliation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registryHasProfessionalRegistrations()
    {
        return $this->hasMany(RegistryHasProfessionalRegistration::class, 'professional_registration_id', 'id');
    }
}
