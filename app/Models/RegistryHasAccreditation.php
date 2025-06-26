<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RegistryHasAccreditation",
 *     type="object",
 *     title="RegistryHasAccreditation",
 *     description="Pivot model representing the relationship between registries and accreditations",
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the registry"
 *     ),
 *     @OA\Property(
 *         property="accreditation_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the accreditation"
 *     )
 * )
 *
 * @property int $registry_id
 * @property int $accreditation_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation whereAccreditationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasAccreditation whereRegistryId($value)
 * @mixin \Eloquent
 */
class RegistryHasAccreditation extends Model
{
    use HasFactory;

    protected $table = 'registry_has_accreditations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'accreditation_id',
    ];
}
