<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\ActionLogType;

/**
 * @OA\Schema (
 *     schema="ActionLog",
 *     title="Action Log",
 *     description="Action Log model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Model primary key"
 *     ),
 *     @OA\Property(
 *         property="entity_type",
 *         type="string",
 *         example="User",
 *         description="Type of the entity associated with the action log"
 *     ),
 *     @OA\Property(
 *         property="entity_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the entity associated with the action log"
 *     ),
 *     @OA\Property(
 *         property="action",
 *         type="string",
 *         example="Updated profile",
 *         description="Description of the action performed"
 *     ),
 *     @OA\Property(
 *         property="completed_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T15:43:00Z",
 *         description="Timestamp when the action was completed (nullable)"
 *     ),
 * )
 *
 * @property int $id
 * @property ActionLogType $entity_type
 * @property int $entity_id
 * @property string $action
 * @property string|null $completed_at
 * @property-read Model|\Eloquent $entity
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActionLog whereId($value)
 * @mixin \Eloquent
 */
class ActionLog extends Model
{
    use HasFactory;

    protected $table = 'action_logs';

    protected $fillable = [
        'entity_id',
        'entity_type',
        'action',
        'completed_at'
    ];

    public $timestamps = false;

    protected $casts = [
        'entity_type' => ActionLogType::class,
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
