<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\SearchManager;

/**
 * @OA\Schema(
 *     schema="Department",
 *     type="object",
 *     title="Department",
 *     description="Model representing departments",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the department"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Research Department",
 *         description="Name of the department"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="string",
 *         example="Health Research",
 *         description="Category of the department"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the department was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the department was last updated"
 *     )
 * )
 * 
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string|null $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Department extends Model
{
    use HasFactory;
    use SearchManager;

    public $table = 'departments';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'category',
    ];

    protected static array $searchableColumns = [
        'name',
    ];

    protected static array $sortableColumns = [
        'name',
    ];
}
