<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Rules",
 *     type="object",
 *     title="Rules",
 *     description="Model representing rules",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the rule"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Data Access Rule",
 *         description="Name of the rule"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         example="Access Control",
 *         description="Title of the rule"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Defines access control policies",
 *         description="Description of the rule"
 *     )
 * )
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
        'description',
    ];

    protected $hidden = ['pivot'];

    /**
     * Get the custodians associated with this rule.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Custodian>
     */
    public function custodians(): BelongsToMany
    {
        return $this->belongsToMany(Custodian::class, 'custodian_has_rules', 'rule_id', 'custodian_id');
    }
}
