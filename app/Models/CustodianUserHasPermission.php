<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustodianUserHasPermission extends Model
{
    use HasFactory;

    public $table = 'custodian_user_has_permissions';

    public $timestamps = false;

    protected $fillable = [
        'custodian_user_id',
        'permission_id',
    ];

    /**
     * Get the permission associated with this custodian user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Permission>
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }
}
