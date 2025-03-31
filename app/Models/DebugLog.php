<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebugLog extends Model
{
    use HasFactory;

    protected $table = 'debug_logs';

    public $timestamps = true;

    protected $fillable = [
        'class',
        'log',
    ];
}
