<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    use HasFactory;

    public $table = 'affiliations';

    public $timestamps = true;

    protected $fillable = [
        'organisation_id',
        'current_employer',
        'member_id',
    ];
}
