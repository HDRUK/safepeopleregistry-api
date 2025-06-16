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
        'user_id',
        'class',
        'log',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
