<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CustodianUserHasPermission;

class CustodianUser extends Model
{
    use HasFactory;

    public $table = 'custodian_users';

    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'provider',
        'keycloak_id',
        'custodian_id',
    ];

    protected $hidden = [
        'password',
        'keycloak_id',
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(
            CustodianUserHasPermission::class,
        );
    }

}
