<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int|null $organisation_id
 * @property string $status
 * @property string|null $invite_accepted_at
 * @property string|null $invite_sent_at
 * @property string|null $invite_code
 * @property-read \App\Models\Organisation|null $organisation
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereInviteAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereInviteCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereInviteSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingInvite whereUserId($value)
 * @mixin \Eloquent
 */
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

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Organisation>
     */
    public function organisation(): HasOne
    {
        return $this->hasOne(
            Organisation::class,
            'organisation_id',
        );
    }

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\User>
     */
    public function user(): HasOne
    {
        return $this->hasOne(
            User::class,
            'user_id',
        );
    }
}
