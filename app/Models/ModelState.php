<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
