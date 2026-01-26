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
        'type',
        'data',
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
}
