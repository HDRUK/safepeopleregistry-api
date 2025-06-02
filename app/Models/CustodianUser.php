<?php

namespace App\Models;

use App\Observers\CustodianUserObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * @OA\Schema(
 *      schema="CustodianUser",
 *      title="Custodian User",
 *      description="CustodianUser model",
 *      @OA\Property(property="id",
 *          type="integer",
 *          example=1,
 *          description="Model primary key"
 *      ),
 *      @OA\Property(property="custodian_id",
 *          type="integer",
 *          example=1,
 *          description="Custodian primary key"
 *      ),
 *      @OA\Property(property="created_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="updated_at",
 *          type="string",
 *          example="2023-10-10T15:03:00Z"
 *      ),
 *      @OA\Property(property="first_name",
 *          type="string",
 *          example="John"
 *      ),
 *      @OA\Property(property="last_name",
 *          type="string",
 *          example="Smith"
 *      ),
 *      @OA\Property(property="email",
 *          type="string",
 *          example="First name"
 *      )
 * )
 */
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
