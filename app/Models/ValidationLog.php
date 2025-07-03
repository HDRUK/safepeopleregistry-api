<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema (
 *     schema="ValidationLog",
 *     title="Validation Log",
 *     description="Validation Log model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Model primary key"
 *     ),
 *     @OA\Property(
 *         property="entity_type",
 *         type="string",
 *         example="App\\Models\\Custodian",
 *         description="Type of the primary entity associated with the validation log"
 *     ),
 *     @OA\Property(
 *         property="entity_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the primary entity associated with the validation log"
 *     ),
 *     @OA\Property(
 *         property="secondary_entity_type",
 *         type="string",
 *         example="App\\Models\\Project",
 *         description="Type of the secondary entity associated with the validation log"
 *     ),
 *     @OA\Property(
 *         property="secondary_entity_id",
 *         type="integer",
 *         example=2,
 *         description="ID of the secondary entity associated with the validation log"
 *     ),
 *     @OA\Property(
 *         property="tertiary_entity_type",
 *         type="string",
 *         example="App\\Models\\Registry",
 *         description="Type of the tertiary entity associated with the validation log"
 *     ),
 *     @OA\Property(
 *         property="tertiary_entity_id",
 *         type="integer",
 *         example=3,
 *         description="ID of the tertiary entity associated with the validation log"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Validation Check 1",
 *         description="Name of the validation log entry"
 *     ),
 *     @OA\Property(
 *         property="completed_at",
 *         type="string",
 *         format="date-time",
 *         example="2023-10-10T15:43:00Z",
 *         description="Timestamp when the validation was completed (nullable)"
 *     ),
 *     @OA\Property(
 *         property="manually_confirmed",
 *         type="boolean",
 *         example=true,
 *         description="Whether the validation was manually confirmed"
 *     )
 * )
 *
 * @property int $id
 * @property string $entity_type
 * @property int|null $validation_check_id
 * @property int $entity_id
 * @property string|null $secondary_entity_type
 * @property int|null $secondary_entity_id
 * @property string|null $tertiary_entity_type
 * @property int|null $tertiary_entity_id
 * @property string|null $completed_at
 * @property int $manually_confirmed
 * @property int $enabled
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ValidationLogComment> $comments
 * @property-read int|null $comments_count
 * @property-read Model|\Eloquent $entity
 * @property-read \App\Models\ValidationCheck|null $validationCheck
 * @method static Builder<static>|ValidationLog newModelQuery()
 * @method static Builder<static>|ValidationLog newQuery()
 * @method static Builder<static>|ValidationLog query()
 * @method static Builder<static>|ValidationLog whereCompletedAt($value)
 * @method static Builder<static>|ValidationLog whereEnabled($value)
 * @method static Builder<static>|ValidationLog whereEntityId($value)
 * @method static Builder<static>|ValidationLog whereEntityType($value)
 * @method static Builder<static>|ValidationLog whereId($value)
 * @method static Builder<static>|ValidationLog whereManuallyConfirmed($value)
 * @method static Builder<static>|ValidationLog whereSecondaryEntityId($value)
 * @method static Builder<static>|ValidationLog whereSecondaryEntityType($value)
 * @method static Builder<static>|ValidationLog whereTertiaryEntityId($value)
 * @method static Builder<static>|ValidationLog whereTertiaryEntityType($value)
 * @method static Builder<static>|ValidationLog whereValidationCheckId($value)
 * @method static Builder<static>|ValidationLog withDisabled()
 * @mixin \Eloquent
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
        'validation_check_id',
        'completed_at',
        'manually_confirmed'
    ];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::addGlobalScope('enabled', function (Builder $builder) {
            $builder->where('enabled', 1);
        });
    }

    public function scopeWithDisabled(Builder $query): Builder
    {
        return $query->withoutGlobalScope('enabled');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function tertiaryEntity(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'tertiary_entity_type', 'tertiary_entity_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\ValidationLogComment>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ValidationLogComment::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\ValidationCheck>
     */
    public function validationCheck(): BelongsTo
    {
        return $this->belongsTo(ValidationCheck::class);
    }
}
