<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRole extends Model
{
    use HasFactory;

    protected $table = 'project_roles';

    public $timestamps = true;

    protected $fillable = [
        'name',
    ];
}
