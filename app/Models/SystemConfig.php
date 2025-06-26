<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="SystemConfig",
 *     type="object",
 *     title="SystemConfig",
 *     description="Model representing system configuration settings",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the system configuration"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="site_name",
 *         description="Name of the configuration setting"
 *     ),
 *     @OA\Property(
 *         property="value",
 *         type="string",
 *         example="Health Data Research UK",
 *         description="Value of the configuration setting"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="The name of the site",
 *         description="Description of the configuration setting"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the configuration was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-26T12:00:00Z",
 *         description="Timestamp when the configuration was last updated"
 *     )
 * )
 * 
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $value
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereValue($value)
 * @mixin \Eloquent
 */
class SystemConfig extends Model
{
    use HasFactory;

    protected $table = 'system_config';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'value',
        'description',
    ];
}
