<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistryHasHistory extends Model
{
    use HasFactory;

    protected $table = 'registry_has_histories';

    public $timestamps = false;

    protected $fillable = [
        'registry_id',
        'history_id',
    ];
}
