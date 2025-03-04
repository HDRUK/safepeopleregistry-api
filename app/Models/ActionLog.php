<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\ActionLogType;

/**
 * @OA\Schema(
 *     schema="ActionLog",
 *     title="Action Log",
 *     description="Action Log model",
 *     @OA\Property(property="id", type="integer", example=1, description="Model primary key"),
 *     @OA\Property(property="entity_type", type="string", example="User", description="Type of the entity associated with the action log"),
 *     @OA\Property(property="entity_id", type="integer", example=1, description="ID of the entity associated with the action log"),
 *     @OA\Property(property="action", type="string", example="Updated profile", description="Description of the action performed"),
 *     @OA\Property(property="type", type="string", example="MODIFICATION", description="Type of action log event"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", example="2023-10-10T15:43:00Z", description="Timestamp when the action was completed (nullable)"),
 * )
 */
class ActionLog extends Model
{
    use HasFactory;

    protected $fillable = ['entity_id', 'entity_type', 'action', 'type', 'completed_at'];

    public $timestamps = false;

    protected $casts = [
        'type' => ActionLogType::class,
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
