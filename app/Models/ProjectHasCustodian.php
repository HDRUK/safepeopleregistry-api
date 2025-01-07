<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHasCustodian extends Model
{
    use HasFactory;

    protected $table = 'project_has_custodians';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'data_custodian_id',
        'approved',
    ];
}
