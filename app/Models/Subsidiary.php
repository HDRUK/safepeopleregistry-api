<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Subsidiary",
 *     type="object",
 *     title="Subsidiary",
 *     description="Model representing subsidiaries",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the subsidiary"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Subsidiary Name",
 *         description="Name of the subsidiary"
 *     ),
 *     @OA\Property(
 *         property="address_1",
 *         type="string",
 *         example="123 Main Street",
 *         description="Primary address line of the subsidiary"
 *     ),
 *     @OA\Property(
 *         property="address_2",
 *         type="string",
 *         example="Suite 456",
 *         description="Secondary address line of the subsidiary"
 *     ),
 *     @OA\Property(
 *         property="town",
 *         type="string",
 *         example="Townsville",
 *         description="Town where the subsidiary is located"
 *     ),
 *     @OA\Property(
 *         property="county",
 *         type="string",
 *         example="Countyshire",
 *         description="County where the subsidiary is located"
 *     ),
 *     @OA\Property(
 *         property="country",
 *         type="string",
 *         example="United Kingdom",
 *         description="Country where the subsidiary is located"
 *     ),
 *     @OA\Property(
 *         property="postcode",
 *         type="string",
 *         example="AB12 3CD",
 *         description="Postcode of the subsidiary"
 *     )
 * )
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
