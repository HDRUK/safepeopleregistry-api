<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *  @OA\Schema(
 *     schema="File",
 *     type="object",
 *     title="File",
 *     description="Model representing files",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the file"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="Researcher List",
 *         description="Name of the file"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         example="researcher_list",
 *         description="Type of the file"
 *     ),
 *     @OA\Property(
 *         property="path",
 *         type="string",
 *         example="/uploads/researcher_list.csv",
 *         description="Path to the file"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="processed",
 *         description="Status of the file"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the file was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the file was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $type
 * @property string $path
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class File extends Model
{
    use HasFactory;

    protected $table = 'files';

    public $timestamps = true;

    public const FILE_STATUS_PENDING = 'pending';
    public const FILE_STATUS_PROCESSED = 'processed';
    public const FILE_STATUS_FAILED = 'failed';

    public const FILE_TYPE_RESEARCHER_LIST = 'researcher_list';
    public const FILE_TYPE_CV = 'cv';
    public const FILE_TYPE_TRAINING_EVIDENCE = 'training_evidence';
    public const FILE_TYPE_DECLARATION_SRO = 'declaration_sro';

    protected $fillable = [
        'name',
        'type',
        'path',
        'status',
    ];
}
