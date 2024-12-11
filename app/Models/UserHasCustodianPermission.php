<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasCustodianPermission extends Model
{
    use HasFactory;

    protected $table = 'user_has_custodian_permissions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'permission_id',
        'custodian_id',
    ];
}
