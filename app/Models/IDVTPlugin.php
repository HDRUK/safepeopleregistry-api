<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *     schema="IDVTPlugin",
 *     type="object",
 *     title="IDVTPlugin",
 *     description="Model representing IDVT plugins",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the IDVT plugin"
 *     ),
 *     @OA\Property(
 *         property="function",
 *         type="string",
 *         example="verifyIdentity",
 *         description="Function name of the plugin"
 *     ),
 *     @OA\Property(
 *         property="args",
 *         type="string",
 *         example="{'key': 'value'}",
 *         description="Arguments passed to the plugin function"
 *     ),
 *     @OA\Property(
 *         property="config",
 *         type="string",
 *         example="{'timeout': 30}",
 *         description="Configuration settings for the plugin"
 *     ),
 *     @OA\Property(
 *         property="enabled",
 *         type="integer",
 *         example=1,
 *         description="Indicates whether the plugin is enabled (1 for enabled, 0 for disabled)"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the plugin was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the plugin was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $function
 * @property string $args
 * @property string $config
 * @property int $enabled
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereArgs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereFunction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IDVTPlugin extends Model
{
    use HasFactory;

    public $table = 'idvt_plugins';

    public $timestamps = true;

    protected $fillable = [
        'function',
        'config',
        'enabled',
    ];
}
