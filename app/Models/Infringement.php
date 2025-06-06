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
 * @property int $reported_by
 * @property string|null $comment
 * @property int $raised_against
 * @method static \Database\Factories\InfringementFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereRaisedAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereReportedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Infringement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Infringement extends Model
{
    use HasFactory;

    protected $table = 'infringements';

    public $timestamps = true;

    protected $fillable = [
        'reported_by',
        'comment',
        'raised_against',
    ];
}
