<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *     schema="Experience",
 *     type="object",
 *     title="Experience",
 *     description="Model representing experiences",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the experience"
 *     ),
 *     @OA\Property(
 *         property="project_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the project associated with the experience"
 *     ),
 *     @OA\Property(
 *         property="from",
 *         type="string",
 *         format="date",
 *         example="2020-01-01",
 *         description="Start date of the experience"
 *     ),
 *     @OA\Property(
 *         property="to",
 *         type="string",
 *         format="date",
 *         example="2022-12-31",
 *         description="End date of the experience"
 *     ),
 *     @OA\Property(
 *         property="organisation_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the organisation associated with the experience"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the experience was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the experience was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $project_id
 * @property string $from
 * @property string $to
 * @property int $organisation_id
 * @method static \Database\Factories\ExperienceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Experience whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Experience extends Model
{
    use HasFactory;

    protected $table = 'experiences';

    public $timestamps = true;

    protected $fillable = [
        'project_id',
        'from',
        'to',
        'organisation_id',
    ];
}
