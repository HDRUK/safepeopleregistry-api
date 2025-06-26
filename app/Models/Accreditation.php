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
 *         property="awarded_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Date when the accreditation was awarded"
 *     ),
 *     @OA\Property(
 *         property="awarding_body_name",
 *         type="string",
 *         example="Health Research Authority",
 *         description="Name of the awarding body"
 *     ),
 *     @OA\Property(
 *         property="awarding_body_ror",
 *         type="string",
 *         example="https://ror.org/12345",
 *         description="ROR identifier for the awarding body"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         example="ISO 27001 Certification",
 *         description="Title of the accreditation"
 *     ),
 *     @OA\Property(
 *         property="expires_at",
 *         type="string",
 *         format="date-time",
 *         example="2026-06-25T12:00:00Z",
 *         description="Date when the accreditation expires"
 *     ),
 *     @OA\Property(
 *         property="awarded_locale",
 *         type="string",
 *         example="UK",
 *         description="Locale where the accreditation was awarded"
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
 * @property string $awarded_at
 * @property string $awarding_body_name
 * @property string|null $awarding_body_ror
 * @property string $title
 * @property string $expires_at
 * @property string $awarded_locale
 * @method static \Database\Factories\AccreditationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAwardedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAwardedLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAwardingBodyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereAwardingBodyRor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Accreditation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Accreditation extends Model
{
    use HasFactory;

    public $table = 'accreditations';

    public $timestamps = true;

    protected $fillable = [
        'awarded_at',
        'awarding_body_name',
        'awarding_body_ror',
        'title',
        'expires_at',
        'awarded_locale',
    ];
}
