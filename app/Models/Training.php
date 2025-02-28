<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

class Training extends Model
{
    use HasFactory;
    use SearchManager;

    public $timestamps = true;

    protected $table = 'trainings';

    protected $fillable = [
        'registry_id',
        'provider',
        'awarded_at',
        'expires_at',
        'expires_in_years',
        'training_name',
        'certification_id',
        'pro_registration',
    ];

    protected static array $searchableColumns = [
        'provider',
        'training_name',
    ];

    protected static array $sortableColumns = [
        'provider',
        'training_name',
    ];
}
