<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RegistryHasEducation",
 *     type="object",
 *     title="RegistryHasEducation",
 *     description="Pivot model representing the relationship between registries and educations",
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the registry"
 *     ),
 *     @OA\Property(
 *         property="education_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the education"
 *     )
 * )
 *
 * @property int $registry_id
 * @property int $education_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation whereEducationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasEducation whereRegistryId($value)
 * @mixin \Eloquent
 */
class RegistryHasEducation extends Model
{
    use HasFactory;

    public $table = 'registry_has_educations';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'education_id',
    ];
}
