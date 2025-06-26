<?php

namespace App\Models;

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
 * @property-read \App\Models\Permission $permission
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
     * Get the permission associated with this custodian user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Permission>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
