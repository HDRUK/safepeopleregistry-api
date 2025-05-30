<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Observers\UserHasDepartmentsObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([UserHasDepartmentsObserver::class])]
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
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the department that belongs to this relationship.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
