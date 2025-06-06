<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 *
 *
 * @property int $id
 * @property string $registration_id
 * @property string $name
 * @property string|null $website
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $town
 * @property string|null $county
 * @property string|null $country
 * @property string|null $postcode
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organisation> $organisations
 * @property-read int|null $organisations_count
 * @method static \Database\Factories\CharityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereRegistrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Charity whereWebsite($value)
 * @mixin \Eloquent
 */
class Charity extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'name',
        'website',
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
    ];

    public $timestamps = false;

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Organisation>
     */
    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class, 'organisation_has_charity');
    }
}
