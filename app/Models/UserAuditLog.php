<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $class
 * @property string $log
 * @property int $user_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAuditLog whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAuditLog whereLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserAuditLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserAuditLog extends Model
{
    use HasFactory;

    protected $table = 'user_audit_logs';

    protected $fillable = [
        'auditor_user_id',
        'user_id',
        'entity',
        'entity_id',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check() && !$model->auditor_user_id) {
                $model->auditor_user_id = Auth::id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audit_user_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
