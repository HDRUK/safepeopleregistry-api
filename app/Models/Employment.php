<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employment extends Model
{
    use HasFactory;

    protected $table = 'employments';

    public $timestamps = true;

    protected $fillable = [
        'employer_name',
        'from',
        'to',
        'is_current',
        'department',
        'role',
        'employer_address',
        'ror',
    ];
}
