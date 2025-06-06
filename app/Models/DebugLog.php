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
 * @property string $class
 * @property string $log
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DebugLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DebugLog extends Model
{
    use HasFactory;

    protected $table = 'debug_logs';

    public $timestamps = true;

    protected $fillable = [
        'class',
        'log',
    ];
}
