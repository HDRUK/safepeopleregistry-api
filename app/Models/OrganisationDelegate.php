<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationDelegate extends Model
{
    use HasFactory;

    public $table = 'organisation_delegates';

    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'is_dpo',
        'is_hr',
        'email',
        'priority_order',
        'organisation_id',
    ];

    protected $hidden = [
        'email',
        'is_dpo',
        'is_hr',
    ];
}
