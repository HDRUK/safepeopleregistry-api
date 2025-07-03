<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OrganisationHasCustodianPermission",
 *     type="object",
 *     title="OrganisationHasCustodianPermission",
 *     description="Pivot model representing the relationship between organisations, custodians, and permissions",
 *     @OA\Property(
 *         property="organisation_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the organisation"
 *     ),
 *     @OA\Property(
 *         property="permission_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the permission"
 *     ),
 *     @OA\Property(
 *         property="custodian_id",
 *         type="integer",
 *         example=12,
 *         description="ID of the custodian"
 *     )
 * )
 *
 * @property int $organisation_id
 * @property int $permission_id
 * @property int $custodian_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCustodianPermission wherePermissionId($value)
 * @mixin \Eloquent
 */
class OrganisationHasCustodianPermission extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_custodian_permissions';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'permission_id',
        'custodian_id',
    ];
}
