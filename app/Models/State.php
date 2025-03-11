<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    protected $table = 'states';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'slug',
    ];

    public const STATE_PENDING = 'pending';
    public const STATE_FORM_RECEIVED = 'form_received';
    public const STATE_VALIDATION_IN_PROGRESS = 'validation_in_progress';
    public const STATE_VALIDATION_COMPLETE = 'validation_complete';
    public const STATE_MORE_USER_INFO_REQ = 'more_user_info_req';
    public const STATE_ESCALATE_VALIDATION = 'escalate_validation';
    public const STATE_VALIDATED = 'validated';

    public const STATES = [
        self::STATE_PENDING,
        self::STATE_FORM_RECEIVED,
        self::STATE_VALIDATION_IN_PROGRESS,
        self::STATE_VALIDATION_COMPLETE,
        self::STATE_MORE_USER_INFO_REQ,
        self::STATE_ESCALATE_VALIDATION,
        self::STATE_VALIDATED,
    ];
}
