<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
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
