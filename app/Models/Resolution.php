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
 * @property string $comment
 * @property int $custodian_by
 * @property int $registry_id
 * @property bool $resolved
 * @method static \Database\Factories\ResolutionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereCustodianBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resolution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Resolution extends Model
{
    use HasFactory;

    public $table = 'resolutions';

    public $timestamps = true;

    protected $fillable = [
        'comment',
        'custodian_by',
        'registry_id',
        'resolved',
    ];

    protected $casts = [
        'resolved' => 'boolean',
    ];
}
