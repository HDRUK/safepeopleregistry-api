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
 * @property string $name
 * @property string $value
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemConfig whereValue($value)
 * @mixin \Eloquent
 */
class SystemConfig extends Model
{
    use HasFactory;

    protected $table = 'system_config';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'value',
        'description',
    ];
}
