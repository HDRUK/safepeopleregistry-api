<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *     schema="Accreditation",
 *     type="object",
 *     title="Accreditation",
 *     description="Accreditation model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the accreditation"
 *     ),
 *     @OA\Property(
 *         property="associated_organisation_name",
 *         type="string",
 *         example="University of Example",
 *         description="Name of the associated organisation"
 *     ),
 *     @OA\Property(
 *         property="id_string",
 *         type="string",
 *         example="123456",
 *         description="ID string for the accreditation"
 *     ),
 *     @OA\Property(
 *         property="issue_date",
 *         type="string",
 *         format="date",
 *         example="2023-01-01",
 *         description="Date when the accreditation was issued"
 *     ),
 *     @OA\Property(
 *         property="expiry_date",
 *         type="string",
 *         format="date",
 *         example="2026-06-25",
 *         description="Date when the accreditation expires"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the accreditation was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the accreditation was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $associated_organisation_name
 * @property string $id_string
 * @property string $issue_date
 * @property string $expiry_date
 * @method static \Database\Factories\AccreditationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAssociatedOrganisationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereIdString($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Accreditation extends Model
{
    use HasFactory;

    public $table = 'accreditations';

    public $timestamps = true;

    protected $fillable = [
        'associated_organisation_name',
        'id_string',
        'issue_date',
        'expiry_date',
    ];
}
