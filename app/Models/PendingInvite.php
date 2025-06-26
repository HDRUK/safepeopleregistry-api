<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @OA\Schema(
 *     schema="PendingInvite",
 *     type="object",
 *     title="PendingInvite",
 *     description="Model representing pending invites",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the pending invite"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=42,
 *         description="ID of the user associated with the invite"
 *     ),
 *     @OA\Property(
 *         property="organisation_id",
 *         type="integer",
 *         example=24,
 *         description="ID of the organisation associated with the invite"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         example="pending",
 *         description="Status of the invite"
 *     ),
 *     @OA\Property(
 *         property="invite_accepted_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the invite was accepted"
 *     ),
 *     @OA\Property(
 *         property="invite_sent_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-24T12:00:00Z",
 *         description="Timestamp when the invite was sent"
 *     ),
 *     @OA\Property(
 *         property="invite_code",
 *         type="string",
 *         example="ABC123",
 *         description="Unique code for the invite"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-23T12:00:00Z",
 *         description="Timestamp when the invite record was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the invite record was last updated"
 *     )
 * )
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
        'invite_sent_at',
        'invite_code',
    ];

    /**
     * Get the organisation associated with this invite.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\Organisation>
     */
    public function organisation(): HasOne
    {
        return $this->hasOne(Organisation::class, 'organisation_id');
    }

    /**
     * Get the user associated with this invite.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Models\User>
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_id');
    }
}
