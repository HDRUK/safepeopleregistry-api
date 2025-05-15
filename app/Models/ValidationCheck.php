<?php

namespace App\Models;

use App\Enums\ValidationCheckAppliesTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'applies_to',
    ];

    protected $casts = [
        'applies_to' => ValidationCheckAppliesTo::class,
    ];

    public function custodians()
    {
        return $this->belongsToMany(Custodian::class, 'custodian_validation_check')
            ->withTimestamps();
    }

    public function scopeForContext($query, ValidationCheckAppliesTo $context)
    {
        return $query->where('applies_to', $context);
    }
}
