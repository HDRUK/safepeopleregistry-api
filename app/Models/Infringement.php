<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *     schema="Infringement",
 *     type="object",
 *     title="Infringement",
 *     description="Model representing infringements",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the infringement"
 *     ),
 *     @OA\Property(
 *         property="reported_by",
 *         type="integer",
 *         example=42,
 *         description="ID of the user who reported the infringement"
 *     ),
 *     @OA\Property(
 *         property="comment",
 *         type="string",
 *         example="This is a comment regarding the infringement.",
 *         description="Optional comment provided by the reporter"
 *     ),
 *     @OA\Property(
 *         property="raised_against",
 *         type="integer",
 *         example=24,
 *         description="ID of the entity the infringement is raised against"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the infringement was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the infringement was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $reported_by
 * @property string|null $comment
 * @property int $raised_against
 * @method static \Database\Factories\InfringementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereRaisedAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereReportedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Infringement extends Model
{
    use HasFactory;

    protected $table = 'infringements';

    public $timestamps = true;

    protected $fillable = [
        'reported_by',
        'comment',
        'raised_against',
    ];
}
