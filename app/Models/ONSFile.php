<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *     schema="ONSFile",
 *     type="object",
 *     title="ONSFile",
 *     description="Model representing ONS files",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the ONS file"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="ONS Data File",
 *         description="Name of the ONS file"
 *     ),
 *     @OA\Property(
 *         property="path",
 *         type="string",
 *         example="/uploads/ons_data.csv",
 *         description="Path to the ONS file"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="processed",
 *         description="Status of the ONS file"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the ONS file was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the ONS file was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $path
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ONSFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ONSFile extends Model
{
    use HasFactory;

    public $table = 'ons_files';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'path',
        'status',
    ];
}
