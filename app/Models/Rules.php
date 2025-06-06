<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Custodian> $custodians
 * @property-read int|null $custodians_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rules whereTitle($value)
 * @mixin \Eloquent
 */
class Rules extends Model
{
    use HasFactory;

    protected $table = 'rules';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'title',
        'description'
    ];

    protected $hidden = ['pivot'];

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Custodian>
     */
    public function custodians(): BelongsToMany
    {
        return $this->belongsToMany(Custodian::class, 'custodian_has_rules', 'rule_id', 'custodian_id');
    }
}
