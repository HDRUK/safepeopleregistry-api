<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RegistryReadRequest",
 *     type="object",
 *     title="RegistryReadRequest",
 *     description="Model representing registry read requests",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the registry read request"
 *     ),
 *     @OA\Property(
 *         property="custodian_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the custodian associated with the read request"
 *     ),
 *     @OA\Property(
 *         property="registry_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the registry associated with the read request"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="integer",
 *         example=0,
 *         description="Status of the read request (0 for open, 1 for approved, 2 for rejected)"
 *     ),
 *     @OA\Property(
 *         property="approved_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the read request was approved"
 *     ),
 *     @OA\Property(
 *         property="rejected_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-26T12:00:00Z",
 *         description="Timestamp when the read request was rejected"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-24T12:00:00Z",
 *         description="Timestamp when the read request was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the read request was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $custodian_id
 * @property int $registry_id
 * @property int $status
 * @property string|null $approved_at
 * @property string|null $rejected_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereRegistryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RegistryReadRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RegistryReadRequest extends Model
{
    use HasFactory;

    protected $table = 'registry_read_requests';

    public $timestamps = true;

    protected $fillable = [
        'custodian_id',
        'registry_id',
        'status',
        'approved_at',
        'rejected_at',
    ];

    public const READ_REQUEST_STATUS_OPEN = 0;
    public const READ_REQUEST_STATUS_APPROVED = 1;
    public const READ_REQUEST_STATUS_REJECTED = 2;
}
