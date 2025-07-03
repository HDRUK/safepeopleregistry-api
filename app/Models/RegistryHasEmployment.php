<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RegistryHasEmployment",
 *     type="object",
 *     title="RegistryHasEmployment",
 *     description="Pivot model representing the relationship between registries and employments",
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the registry"
 *     ),
 *     @OA\Property(
 *         property="employment_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the employment"
 *     )
 * )
 *
 * @property int $registry_id
 * @property int $employment_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment whereEmploymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEmployment whereRegistryId($value)
 * @mixin \Eloquent
 */
class RegistryHasEmployment extends Model
{
    use HasFactory;

    public $table = 'registry_has_employments';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'employment_id',
    ];
}
