<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read \App\Models\State $state
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
