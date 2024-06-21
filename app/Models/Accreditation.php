<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accreditation extends Model
{
    use HasFactory;

    public $table = 'accreditations';

    public $timestamps = true;

    protected $fillable = [
        'awarded_at',
        'awarding_body_name',
        'awarding_body_ror',
        'title',
        'expires_at',
        'awarded_locale',
    ];
}
