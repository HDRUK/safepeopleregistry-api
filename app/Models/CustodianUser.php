<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

class CustodianUser extends Model
{
    use HasFactory;
    use SearchManager;

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

    protected static array $searchableColumns = [
        'first_name',
        'last_name',
        'email'
    ];

    protected static array $sortableColumns = [
        'first_name',
        'last_name',
        'email'
    ];

    /**
     * Get the permissions associated with the custodian user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userPermissions()
    {
        return $this->hasMany(CustodianUserHasPermission::class, 'custodian_user_id', 'id');
    }

    /**
     * Get the custodian that owns the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function custodian()
    {
        return $this->belongsTo(Custodian::class, 'custodian_id', 'id');
    }
}
