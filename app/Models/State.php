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

    public const STATE_REGISTERED = 'registered';
    public const STATE_PENDING = 'pending';
    public const STATE_FORM_RECEIVED = 'form_received';
    public const STATE_VALIDATION_IN_PROGRESS = 'validation_in_progress';
    public const STATE_VALIDATION_COMPLETE = 'validation_complete';
    public const STATE_MORE_USER_INFO_REQ = 'more_user_info_req';
    public const STATE_ESCALATE_VALIDATION = 'escalate_validation';
    public const STATE_VALIDATED = 'validated';
    public const STATE_APPROVED = 'approved';
    public const STATE_PROJECT_PENDING = 'project_pending';
    public const STATE_PROJECT_COMPLETED = 'project_completed';
    public const STATE_PROJECT_APPROVED = 'project_approved';
    public const STATE_AFFILIATION_INVITED = 'affiliation_invited';
    public const STATE_AFFILIATION_PENDING = 'affiliation_pending';
    public const STATE_AFFILIATION_APPROVED = 'affiliation_approved';
    public const STATE_AFFILIATION_REJECTED = 'affiliation_rejected';

    public const STATES = [
        self::STATE_REGISTERED,
        self::STATE_PENDING,
        self::STATE_FORM_RECEIVED,
        self::STATE_VALIDATION_IN_PROGRESS,
        self::STATE_VALIDATION_COMPLETE,
        self::STATE_MORE_USER_INFO_REQ,
        self::STATE_ESCALATE_VALIDATION,
        self::STATE_VALIDATED,
        self::STATE_PROJECT_APPROVED,
        self::STATE_PROJECT_COMPLETED,
        self::STATE_PROJECT_PENDING,
        self::STATE_AFFILIATION_INVITED,
        self::STATE_AFFILIATION_PENDING,
        self::STATE_AFFILIATION_APPROVED,
        self::STATE_AFFILIATION_REJECTED,
    ];
}
