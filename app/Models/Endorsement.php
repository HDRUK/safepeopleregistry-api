<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endorsement extends Model
{
    use HasFactory;

    protected $table = 'endorsements';

    public $timestamps = true;

    protected $fillable = [
        'reported_by',
        'comment',
        'raised_against',
    ];
}
