<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
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
