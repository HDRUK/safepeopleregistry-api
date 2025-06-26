<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="DebugLog",
 *     type="object",
 *     title="DebugLog",
 *     description="Model representing debug logs",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the debug log"
 *     ),
 *     @OA\Property(
 *         property="class",
 *         type="string",
 *         example="App\Http\Controllers\Api\V1\CustodianController",
 *         description="Class name where the log was generated"
 *     ),
 *     @OA\Property(
 *         property="log",
 *         type="string",
 *         example="An error occurred while processing the request.",
 *         description="Log message"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the log was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the log was last updated"
 *     )
 * )
 * 
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $class
 * @property string $log
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DebugLog extends Model
{
    use HasFactory;

    protected $table = 'debug_logs';

    public $timestamps = true;

    protected $fillable = [
        'class',
        'log',
    ];
}
