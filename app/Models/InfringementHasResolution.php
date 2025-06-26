<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="InfringementHasResolution",
 *     type="object",
 *     title="InfringementHasResolution",
 *     description="Pivot model representing the relationship between infringements and resolutions",
 *     @OA\Property(
 *         property="infringement_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the infringement"
 *     ),
 *     @OA\Property(
 *         property="resolution_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the resolution"
 *     )
 * )
 * 
 * @property int $infringement_id
 * @property int $resolution_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution whereInfringementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution whereResolutionId($value)
 * @mixin \Eloquent
 */
class InfringementHasResolution extends Model
{
    use HasFactory;

    public $table = 'infringement_has_resolutions';

    public $timestamps = false;

    protected $fillable = [
        'infringement_id',
        'resolution_id',
    ];
}
