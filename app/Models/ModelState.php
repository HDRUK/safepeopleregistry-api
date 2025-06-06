<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read \App\Models\State $state
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $state_id
 * @property string $stateable_type
 * @property int $stateable_id
 * @property-read Model|\Eloquent $stateable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereStateableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereStateableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModelState whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ModelState extends Model
{
    use HasFactory;

    protected $table = 'model_states';
    protected $fillable = [
        'state_id',
    ];

    public function stateable()
    {
        return $this->morphTo();
    }

    /**
     * @property-read \App\Models\State $state
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
