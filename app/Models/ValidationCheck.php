<?php

namespace App\Models;

use App\Enums\ValidationCheckAppliesTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

class ValidationCheck extends Model
{
    use HasFactory;
    use SearchManager;

    protected $fillable = [
        'name',
        'description',
        'applies_to',
        'enabled'
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
