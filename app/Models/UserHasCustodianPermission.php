<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $user_id
 * @property int $permission_id
 * @property int $custodian_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianPermission whereUserId($value)
 * @mixin \Eloquent
 */
class UserHasCustodianPermission extends Model
{
    use HasFactory;

    protected $table = 'user_has_custodian_permissions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'permission_id',
        'custodian_id',
    ];
}
