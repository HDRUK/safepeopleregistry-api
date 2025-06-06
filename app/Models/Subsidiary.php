<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $town
 * @property string|null $county
 * @property string|null $country
 * @property string|null $postcode
 * @method static \Database\Factories\SubsidiaryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereCounty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subsidiary whereTown($value)
 * @mixin \Eloquent
 */
class Subsidiary extends Model
{
    use HasFactory;

    protected $table = 'subsidiaries';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'address_1',
        'address_2',
        'town',
        'county',
        'country',
        'postcode',
    ];

}
