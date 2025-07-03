<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="OrganisationHasCharity",
 *     type="object",
 *     title="OrganisationHasCharity",
 *     description="Pivot model representing the relationship between organisations and charities",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the organisation-charity relationship"
 *     ),
 *     @OA\Property(
 *         property="organisation_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the organisation"
 *     ),
 *     @OA\Property(
 *         property="charity_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the charity"
 *     )
 * )
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
