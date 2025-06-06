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
 * @property string $function
 * @property string $args
 * @property string $config
 * @property int $enabled
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereArgs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereFunction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IDVTPlugin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IDVTPlugin extends Model
{
    use HasFactory;

    public $table = 'idvt_plugins';

    public $timestamps = true;

    protected $fillable = [
        'function',
        'config',
        'enabled',
    ];
}
