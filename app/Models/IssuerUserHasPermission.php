<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuerUserHasPermission extends Model
{
    use HasFactory;

    public $table = 'issuer_user_has_permissions';

    public $timestamps = false;

    protected $fillable = [
        'issuer_user_id',
        'permission_id',
    ];
}
