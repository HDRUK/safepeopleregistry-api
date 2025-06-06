<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
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
