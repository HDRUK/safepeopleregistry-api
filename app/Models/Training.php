<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'trainings';

    protected $fillable = [
        'registry_id',
        'provider',
        'awarded_at',
        'expires_at',
        'expires_in_years',
        'training_name',
    ];
}
