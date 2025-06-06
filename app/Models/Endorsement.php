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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereRaisedAgainst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereReportedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Endorsement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Endorsement extends Model
{
    use HasFactory;

    protected $table = 'endorsements';

    public $timestamps = true;

    protected $fillable = [
        'reported_by',
        'comment',
        'raised_against',
    ];
}
