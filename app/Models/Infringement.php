<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Infringement extends Model
{
    use HasFactory;

    protected $table = 'infringements';

    public $timestamps = true;

    protected $fillable = [
        'reported_by',
        'comment',
        'raised_against',
    ];
}
