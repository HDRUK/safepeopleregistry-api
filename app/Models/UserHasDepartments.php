<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $user_id
 * @property int $department_id
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserHasDepartments whereUserId($value)
 * @mixin \Eloquent
 */
class UserHasDepartments extends Model
{
    use HasFactory;

    protected $table = 'user_has_departments';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'department_id',
    ];

    /**
     * Get the user that belongs to this relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the department that belongs to this relationship.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Department>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
