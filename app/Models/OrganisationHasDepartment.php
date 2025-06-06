<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $organisation_id
 * @property int $department_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasDepartment whereOrganisationId($value)
 * @mixin \Eloquent
 */
class OrganisationHasDepartment extends Model
{
    use HasFactory;

    public $table = 'organisation_has_departments';

    public $timestamps = false;

    protected $fillable = [
        'organisation_id',
        'department_id',
    ];
}
