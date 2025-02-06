<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasCharity extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_charity';

    protected $fillable = [
        'organisation_id',
        'charity_id',
    ];

    public $timestamps = false;
}
