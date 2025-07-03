<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Endorsement",
 *     type="object",
 *     title="Endorsement",
 *     description="Model representing endorsements",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the endorsement"
 *     ),
 *     @OA\Property(
 *         property="reported_by",
 *         type="integer",
 *         example=42,
 *         description="ID of the user who reported the endorsement"
 *     ),
 *     @OA\Property(
 *         property="comment",
 *         type="string",
 *         example="This is a comment regarding the endorsement.",
 *         description="Optional comment provided by the reporter"
 *     ),
 *     @OA\Property(
 *         property="raised_against",
 *         type="integer",
 *         example=24,
 *         description="ID of the entity the endorsement is raised against"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the endorsement was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the endorsement was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $reported_by
 * @property string|null $comment
 * @property int $raised_against
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereRaisedAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereReportedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Endorsement extends Model
{
    use HasFactory;

    protected $table = 'endorsements';

    public $timestamps = true;

    protected $fillable = [
        'reported_by',
        'comment',
        'raised_against',
    ];
}
