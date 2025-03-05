<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistryHasTraining extends Model
{
    use HasFactory;

    protected $table = 'registry_has_trainings';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'training_id',
    ];
}
