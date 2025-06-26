<?php

namespace App\Models;

use App\Enums\ValidationCheckAppliesTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

/**
 * @OA\Schema(
 *     schema="ValidationCheck",
 *     type="object",
 *     title="Validation Check",
 *     description="Model representing validation checks",
 *     required={"name", "description", "applies_to"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the validation check"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Check format",
 *         description="Name of the validation check"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Ensures proper formatting of input",
 *         description="Description of the validation check"
 *     ),
 *     @OA\Property(
 *         property="applies_to",
 *         type="string",
 *         example="user",
 *         description="Context to which the validation check applies"
 *     ),
 *     @OA\Property(
 *         property="enabled",
 *         type="boolean",
 *         example=true,
 *         description="Indicates whether the validation check is enabled"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-01T00:00:00Z",
 *         description="Timestamp when the validation check was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-01T00:00:00Z",
 *         description="Timestamp when the validation check was last updated"
 *     )
 * )
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property ValidationCheckAppliesTo $applies_to
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Custodian> $custodians
 * @property-read int|null $custodians_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck applySorting()
 * @method static \Database\Factories\ValidationCheckFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck forContext(\App\Enums\ValidationCheckAppliesTo $context)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereAppliesTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ValidationCheck whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ValidationCheck extends Model
{
    use HasFactory;
    use SearchManager;

    protected $table = 'validation_checks';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'applies_to',
        'enabled',
    ];

    protected static array $searchableColumns = [
        'applies_to',
        'name',
        'description',
    ];

    protected static array $sortableColumns = [
        'name',
        'description',
    ];

    protected $casts = [
        'applies_to' => ValidationCheckAppliesTo::class,
    ];

    /**
     * Get the custodians associated with this validation check.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Custodian>
     */
    public function custodians()
    {
        return $this->belongsToMany(Custodian::class, 'custodian_validation_check')
            ->withTimestamps();
    }

    /**
     * Scope a query to filter validation checks by context.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param ValidationCheckAppliesTo $context
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForContext($query, ValidationCheckAppliesTo $context)
    {
        return $query->where('applies_to', $context);
    }
}
