<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ProfessionalRegistration",
 *     type="object",
 *     title="ProfessionalRegistration",
 *     description="Model representing professional registrations",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the professional registration"
 *     ),
 *     @OA\Property(
 *         property="member_id",
 *         type="string",
 *         example="MEM12345",
 *         description="Member ID associated with the professional registration"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Medical Council",
 *         description="Name of the professional registration"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the professional registration was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the professional registration was last updated"
 *     )
 * )
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
     * Get the registry relationships for the professional registration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\RegistryHasProfessionalRegistration>
     */
    public function registryHasProfessionalRegistrations(): HasMany
    {
        return $this->hasMany(RegistryHasProfessionalRegistration::class, 'professional_registration_id', 'id');
    }
}
