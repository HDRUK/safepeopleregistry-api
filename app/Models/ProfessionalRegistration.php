<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $member_id
 * @property string $name
 * @method static \Database\Factories\ProfessionalRegistrationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfessionalRegistration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\RegistryHasProfessionalRegistration>
     */
    public function registryHasProfessionalRegistrations(): HasMany
    {
        return $this->hasMany(RegistryHasProfessionalRegistration::class, 'professional_registration_id', 'id');
    }
}
