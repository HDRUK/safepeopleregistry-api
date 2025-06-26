<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="UserHasCustodianApproval",
 *     type="object",
 *     title="UserHasCustodianApproval",
 *     description="Pivot model representing the relationship between users and custodians for approval",
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the user"
 *     ),
 *     @OA\Property(
 *         property="custodian_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the custodian"
 *     )
 * )
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
