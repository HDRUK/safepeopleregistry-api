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
 * @property string $name
 * @property string $slug
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class State extends Model
{
    use HasFactory;

    protected $table = 'states';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'slug',
    ];

    public const USER_REGISTERED = 'registered';
    public const USER_PENDING = 'pending';
    public const USER_APPROVED = 'approved';    
    public const PROJECT_USER_FORM_RECEIVED = 'form_received';
    public const PROJECT_USER_VALIDATION_IN_PROGRESS = 'validation_in_progress';
    public const PROJECT_USER_VALIDATION_COMPLETE = 'validation_complete';
    public const PROJECT_USER_MORE_USER_INFO_REQ = 'more_user_info_req';
    public const PROJECT_USER_ESCALATE_VALIDATION = 'escalate_validation';
    public const PROJECT_USER_VALIDATED = 'validated';
    public const PROJECT_PENDING = 'project_pending';
    public const PROJECT_COMPLETED = 'project_completed';
    public const PROJECT_APPROVED = 'project_approved';
    public const AFFILIATION_INVITED = 'affiliation_invited';
    public const AFFILIATION_PENDING = 'affiliation_pending';
    public const AFFILIATION_APPROVED = 'affiliation_approved';
    public const AFFILIATION_REJECTED = 'affiliation_rejected';

    public const STATES = [
        self::USER_REGISTERED,
        self::USER_PENDING,
        self::USER_APPROVED,
        self::PROJECT_USER_FORM_RECEIVED,
        self::PROJECT_USER_VALIDATION_IN_PROGRESS,
        self::PROJECT_USER_VALIDATION_COMPLETE,
        self::PROJECT_USER_MORE_USER_INFO_REQ,
        self::PROJECT_USER_ESCALATE_VALIDATION,
        self::PROJECT_USER_VALIDATED,
        self::PROJECT_APPROVED,
        self::PROJECT_COMPLETED,
        self::PROJECT_PENDING,
        self::AFFILIATION_INVITED,
        self::AFFILIATION_PENDING,
        self::AFFILIATION_APPROVED,
        self::AFFILIATION_REJECTED,
    ];
}
