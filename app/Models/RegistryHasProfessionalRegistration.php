<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RegistryHasProfessionalRegistration",
 *     type="object",
 *     title="RegistryHasProfessionalRegistration",
 *     description="Pivot model representing the relationship between registries and professional registrations",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the registry-professional registration relationship"
 *     ),
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the registry"
 *     ),
 *     @OA\Property(
 *         property="professional_registration_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the professional registration"
 *     )
 * )
 * 
 * @property int $id
 * @property int $professional_registration_id
 * @property int $registry_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration whereProfessionalRegistrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasProfessionalRegistration whereRegistryId($value)
 * @mixin \Eloquent
 */
class RegistryHasProfessionalRegistration extends Model
{
    use HasFactory;

    public $table = 'registry_has_professional_registrations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'professional_registration_id',
    ];
}
