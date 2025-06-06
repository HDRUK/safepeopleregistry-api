<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $infringement_id
 * @property int $resolution_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution whereInfringementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InfringementHasResolution whereResolutionId($value)
 * @mixin \Eloquent
 */
class InfringementHasResolution extends Model
{
    use HasFactory;

    public $table = 'infringement_has_resolutions';

    public $timestamps = false;

    protected $fillable = [
        'infringement_id',
        'resolution_id',
    ];
}
