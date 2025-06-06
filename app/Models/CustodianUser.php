<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\CustodianUserHasPermission> $userPermissions
 */
/**
 *
 *
 * @OA\Schema (
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
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $password
 * @property string|null $provider
 * @property string|null $keycloak_id
 * @property int $custodian_id
 * @property-read \App\Models\Custodian|null $custodian
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustodianUserHasPermission> $userPermissions
 * @property-read int|null $user_permissions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser applySorting()
 * @method static \Database\Factories\CustodianUserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereKeycloakId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUser whereUpdatedAt($value)
 * @mixin \Eloquent
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\CustodianUserHasPermission>
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(CustodianUserHasPermission::class, 'custodian_user_id', 'id');
    }

    /**
     * Get the custodian that owns the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Custodian>
     */
    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class, 'custodian_id', 'id');
    }
}
