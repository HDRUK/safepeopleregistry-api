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
