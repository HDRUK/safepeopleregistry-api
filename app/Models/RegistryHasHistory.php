<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RegistryHasHistory",
 *     type="object",
 *     title="RegistryHasHistory",
 *     description="Pivot model representing the relationship between registries and histories",
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the registry"
 *     ),
 *     @OA\Property(
 *         property="history_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the history"
 *     )
 * )
 * 
 * @property int $registry_id
 * @property int $history_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory whereHistoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryHasHistory whereRegistryId($value)
 * @mixin \Eloquent
 */
class RegistryHasHistory extends Model
{
    use HasFactory;

    protected $table = 'registry_has_histories';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'history_id',
    ];
}
