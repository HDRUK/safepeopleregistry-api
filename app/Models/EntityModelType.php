<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
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

    protected $table = 'entity_model_types';
    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    public const ENTITY_TYPES = [
        'decision_models',
        'user_validation_rules',
        'org_validation_rules',
    ];
}
