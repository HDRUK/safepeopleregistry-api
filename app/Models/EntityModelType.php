<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
