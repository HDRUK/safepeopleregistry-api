<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $model_type
 * @property string $conditions
 * @property string $rule_class
 * @property string $description
 * @property int $entity_model_type_id
 * @property-read \App\Models\CustodianModelConfig|null $custodianModelConfig
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereEntityModelTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereRuleClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DecisionModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DecisionModel extends Model
{
    use HasFactory;

    protected $table = 'decision_models';

    public $timestamps = true;

    protected $fillable = [
        'model_type',
        'conditions',
        'rule_class',
    ];

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\CustodianModelConfig>
     */
    public function custodianModelConfig(): HasOne
    {
        return $this->hasOne(
            CustodianModelConfig::class,
            'entity_model_id'
        );
    }
}
