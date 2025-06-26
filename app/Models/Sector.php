<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\SearchManager;

/**
 * @OA\Schema(
 *     schema="Sector",
 *     type="object",
 *     title="Sector",
 *     description="Model representing sectors",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the sector"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Healthcare Providers",
 *         description="Name of the sector"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the sector was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-26T12:00:00Z",
 *         description="Timestamp when the sector was last updated"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-27T12:00:00Z",
 *         description="Timestamp when the sector was deleted"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector applySorting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector filterWhen(string $filter, $callback)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector searchViaRequest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sector withoutTrashed()
 * @mixin \Eloquent
 */
class Sector extends Model
{
    use SoftDeletes;
    use SearchManager;

    protected $table = 'sectors';

    public $timestamps = true;

    protected $fillable = [
        'name',
    ];

    public const SECTORS = [
        'NHS',
        'NGO',
        'Public',
        'Healthcare Providers',
        'Pharmaceutical and Biotechnology Companies (non-SME)',
        'Pharmaceutical and Biotechnology Companies (SME)',
        'Academic Research Institutions',
        'Government Agencies: e.g., DHSC, Regulatory bodies, NICE',
        'Non-Profit Organisations (e.g., foundations, advocacy groups, charities)',
        'Other for-profit organisations (non-SME)',
        'Other for-profit organisations (SME)',
    ];

    protected static array $searchableColumns = [
        'name',
    ];

    protected static array $sortableColumns = [
        'name',
    ];
}
