<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $user_id
 * @property int $custodian_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasCustodianApproval whereUserId($value)
 * @mixin \Eloquent
 */
class UserHasCustodianApproval extends Model
{
    use HasFactory;

    protected $table = 'user_has_custodian_approvals';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'custodian_id',
    ];
}
