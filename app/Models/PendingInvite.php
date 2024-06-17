<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingInvite extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'pending_invites';

    /**
     * Whether or not this model supports timestamps
     * 
     * @var bool
     */
    public $timestamps = true;

    /**
     * What fields of this model are accepted as parameters
     * 
     * @var array
     */
    protected $fillable = [
        'user_id',
        'organisation_id',
        'status',
    ];

    public function organisation(): HasOne
    {
        return $this->hasOne(
            Organisation::class,
            'organisation_id',
        );
    }

    public function user(): HasOne
    {
        return $this->hasOne(
            User::class,
            'user_id',
        );
    }
}
