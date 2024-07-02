<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuerUser extends Model
{
    use HasFactory;

    public $table = 'issuer_users';

    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'provider',
        'keycloak_id',
        'issuer_id',
    ];
}
