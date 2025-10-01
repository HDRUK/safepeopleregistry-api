<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="EntityModelType",
 *     type="object",
 *     title="EntityModelType",
 *     description="Model representing types of entity models",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the entity model type"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Decision Models",
 *         description="Name of the entity model type"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the entity model type was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the entity model type was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EntityModelType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EntityModelType extends Model
{
    use HasFactory;

    public const DECISON_MODELS = 'decision_models';
    public const ORG_VALIDATION_RULES = 'org_validation_rules';
    public const USER_VALIDATION_RULES = 'user_validation_rules';

    protected $table = 'entity_model_types';
    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    public const ENTITY_TYPES = [
        self::DECISON_MODELS,
        self::USER_VALIDATION_RULES,
        self::ORG_VALIDATION_RULES,
    ];
}
