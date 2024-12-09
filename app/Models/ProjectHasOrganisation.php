<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHasOrganisation extends Model
{
    use HasFactory;

    protected $table = 'project_has_organisations';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'organisation_id',
    ];
}
