<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IDVTPlugin extends Model
{
    use HasFactory;

    public $table = 'idvt_plugins';

    public $timestamps = true;

    protected $fillable = [
        'function',
        'config',
        'enabled',
    ];
}
