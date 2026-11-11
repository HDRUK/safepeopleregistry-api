<?php

namespace App\Models;

use App\Enums\CustodianRole;
use App\Exceptions\CustodianRoleConflictException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="CustodianUserHasPermission",
 *     type="object",
 *     title="CustodianUserHasPermission",
 *     description="Model representing the relationship between Custodian Users and Permissions",
 *     @OA\Property(
 *         property="custodian_user_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the custodian user"
 *     ),
 *     @OA\Property(
 *         property="permission_id",
 *         type="integer",
 *         example=2,
 *         description="ID of the permission"
 *     )
 * )
 *
 * @property-read \App\Models\Permission|null $permission
 * @property int $custodian_user_id
 * @property int $permission_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission whereCustodianUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianUserHasPermission wherePermissionId($value)
 * @mixin \Eloquent
 */
class CustodianUserHasPermission extends Model
{
    use HasFactory;

    public $table = 'custodian_user_has_permissions';

    public $timestamps = false;

    protected $fillable = [
        'custodian_user_id',
        'permission_id',
    ];

    /**
     * Custodian roles are mutually exclusive. Every row in this table is created through this
     * model, so refusing a second role here covers controllers, seeders and anything else.
     * Rows that aren't roles (e.g. CUSTODIAN_KEYCARD_*) are unaffected.
     */
    protected static function booted(): void
    {
        static::creating(function (CustodianUserHasPermission $row): void {
            $permission = Permission::find($row->permission_id);

            if (! $permission || CustodianRole::tryFrom($permission->name) === null) {
                return;
            }

            $holdsAnotherRole = static::query()
                ->where('custodian_user_id', $row->custodian_user_id)
                ->where('permission_id', '!=', $permission->id)
                ->whereHas('permission', fn ($query) => $query->whereIn('name', CustodianRole::permissionNames()))
                ->exists();

            if ($holdsAnotherRole) {
                throw new CustodianRoleConflictException();
            }
        });
    }

    /**
     * Get the permission associated with this custodian user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Permission>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
