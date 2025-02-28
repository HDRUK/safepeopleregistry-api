<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PendingInvite extends Model
{
    use HasFactory;

    protected $table = 'pending_invites';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'organisation_id',
        'status',
        'invite_accepted_at',
        'invite_sent_at'
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
