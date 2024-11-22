<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use SoftDeletes;

    protected $table = 'sectors';

    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    public const SECTORS = [
        'NHS',
        'Academia',
        'NGO',
        'Public',
        'Charity/Non-profit',
        'Private/Industry',
    ];
}
