<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHasUser extends Model
{
    use HasFactory;

    protected $table = 'project_has_users';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'user_digital_ident',
    ];
}
