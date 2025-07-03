<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Resolution",
 *     type="object",
 *     title="Resolution",
 *     description="Model representing resolutions",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the resolution"
 *     ),
 *     @OA\Property(
 *         property="comment",
 *         type="string",
 *         example="Resolution comment",
 *         description="Comment associated with the resolution"
 *     ),
 *     @OA\Property(
 *         property="custodian_by",
 *         type="integer",
 *         example=42,
 *         description="ID of the custodian who resolved the issue"
 *     ),
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the registry associated with the resolution"
 *     ),
 *     @OA\Property(
 *         property="resolved",
 *         type="boolean",
 *         example=true,
 *         description="Indicates whether the resolution is resolved"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the resolution was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-26T12:00:00Z",
 *         description="Timestamp when the resolution was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $comment
 * @property int $custodian_by
 * @property int $registry_id
 * @property bool $resolved
 * @method static \Database\Factories\ResolutionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereCustodianBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Resolution extends Model
{
    use HasFactory;

    public $table = 'resolutions';

    public $timestamps = true;

    protected $fillable = [
        'comment',
        'custodian_by',
        'registry_id',
        'resolved',
    ];

    protected $casts = [
        'resolved' => 'boolean',
    ];
}
