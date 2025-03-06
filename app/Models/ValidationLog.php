<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @OA\Schema(
 *     schema="ValidationLog",
 *     title="Validation Log",
 *     description="Validation Log model",
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Model primary key"
 *     ),
 *
 *     @OA\Property(
 *         property="entity_type",
 *         type="string",
 *         example="App\\Models\\Custodian",
 *         description="Type of the primary entity associated with the validation log"
 *     ),
 *
 *     @OA\Property(
 *         property="entity_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the primary entity associated with the validation log"
 *     ),
 *
 *     @OA\Property(
 *         property="secondary_entity_type",
 *         type="string",
 *         example="App\\Models\\Project",
 *         description="Type of the secondary entity associated with the validation log"
 *     ),
 *
 *     @OA\Property(
 *         property="secondary_entity_id",
 *         type="integer",
 *         example=2,
 *         description="ID of the secondary entity associated with the validation log"
 *     ),
 *
 *     @OA\Property(
 *         property="tertiary_entity_type",
 *         type="string",
 *         example="App\\Models\\Registry",
 *         description="Type of the tertiary entity associated with the validation log"
 *     ),
 *
 *     @OA\Property(
 *         property="tertiary_entity_id",
 *         type="integer",
 *         example=3,
 *         description="ID of the tertiary entity associated with the validation log"
 *     ),
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Validation Check 1",
 *         description="Name of the validation log entry"
 *     ),
 *
 *     @OA\Property(
 *         property="completed_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T15:43:00Z",
 *         description="Timestamp when the validation was completed (nullable)"
 *     ),
 *
 *     @OA\Property(
 *         property="manually_confirmed",
 *         type="boolean",
 *         example=true,
 *         description="Whether the validation was manually confirmed"
 *     ),
 * )
 */

class ValidationLog extends Model
{
    use HasFactory;

    protected $table = 'validation_logs';

    protected $fillable = [
        'entity_id',
        'entity_type',
        'secondary_entity_id',
        'secondary_entity_type',
        'tertiary_entity_id',
        'tertiary_entity_type',
        'name',
        'completed_at',
        'manually_confirmed'
    ];

    public $timestamps = false;

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
