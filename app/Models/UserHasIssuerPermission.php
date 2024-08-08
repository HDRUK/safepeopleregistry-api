<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasIssuerPermission extends Model
{
    use HasFactory;

    protected $table = 'user_has_issuer_permissions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'permission_id',
        'issuer_id',
    ];
}
