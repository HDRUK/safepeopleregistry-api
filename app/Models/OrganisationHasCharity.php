<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $charity_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity whereCharityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationHasCharity whereOrganisationId($value)
 * @mixin \Eloquent
 */
class OrganisationHasCharity extends Model
{
    use HasFactory;

    protected $table = 'organisation_has_charity';

    protected $fillable = [
        'organisation_id',
        'charity_id',
    ];

    public $timestamps = false;
}
