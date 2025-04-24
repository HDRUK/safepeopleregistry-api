<?php

namespace App\Models;

use App\Observers\RegistryReadRequestObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([RegistryReadRequestObserver::class])]
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
