<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'to',
        'subject',
        'template',
        'body',
        'job_uuid',
        'job_status',
        'message_id',
        'message_status',
        'message_response',
    ];

    protected $casts = [
        'data' => 'array',
        'job_status' => 'integer',
    ];

    public const EMAIL_STATUS_PROCESSED = 'processed';
    public const EMAIL_STATUS_DELIVERED = 'delivered';
    public const EMAIL_STATUS_DEFERRED = 'deferred';
    public const EMAIL_STATUS_DROPPED = 'dropped';
    public const EMAIL_STATUS_BOUNCED = 'bounced';
    public const EMAIL_STATUS_BLOCKED = 'blocked';
}
