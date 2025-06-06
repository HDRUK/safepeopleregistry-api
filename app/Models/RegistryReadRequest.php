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
