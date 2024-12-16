<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationHasDepartment extends Model
{
    use HasFactory;

    public $table = 'organisation_has_departments';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'department_id',
    ];
}
