<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="UksaLiveFeed",
 *     type="object",
 *     title="UksaLiveFeed",
 *     description="Model representing UKSA live feed data",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the UKSA live feed record"
 *     ),
 *     @OA\Property(
 *         property="first_name",
 *         type="string",
 *         example="John",
 *         description="First name of the individual"
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         example="Doe",
 *         description="Last name of the individual"
 *     ),
 *     @OA\Property(
 *         property="organisation_name",
 *         type="string",
 *         example="Health Data Research UK",
 *         description="Name of the organisation"
 *     ),
 *     @OA\Property(
 *         property="accreditation_number",
 *         type="string",
 *         example="ACC12345",
 *         description="Accreditation number"
 *     ),
 *     @OA\Property(
 *         property="accreditation_type",
 *         type="string",
 *         example="Type A",
 *         description="Type of accreditation"
 *     ),
 *     @OA\Property(
 *         property="expiry_date",
 *         type="string",
 *         format="date",
 *         example="2025-12-31",
 *         description="Expiry date of the accreditation"
 *     ),
 *     @OA\Property(
 *         property="public_record",
 *         type="string",
 *         example="Yes",
 *         description="Indicates whether the record is public"
 *     ),
 *     @OA\Property(
 *         property="stage",
 *         type="string",
 *         example="Approved",
 *         description="Current stage of the accreditation process"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the record was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-26T12:00:00Z",
 *         description="Timestamp when the record was last updated"
 *     )
 * )
 * 
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $first_name
 * @property string $last_name
 * @property string $organisation_name
 * @property string $accreditation_number
 * @property string $accreditation_type
 * @property string $expiry_date
 * @property string $public_record
 * @property string $stage
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereAccreditationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereAccreditationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereOrganisationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed wherePublicRecord($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereStage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UksaLiveFeed whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UksaLiveFeed extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'uksa_live_feeds';

    protected $fillable = [
        'first_name',
        'last_name',
        'organisation_name',
        'accreditation_number',
        'accreditation_type',
        'expiry_date',
        'public_record',
        'stage',
    ];
}
