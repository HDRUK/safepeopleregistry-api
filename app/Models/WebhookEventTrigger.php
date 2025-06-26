<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="WebhookEventTrigger",
 *     type="object",
 *     title="WebhookEventTrigger",
 *     description="Model representing webhook event triggers",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the webhook event trigger"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         example="User Joined Project",
 *         description="Name of the webhook event trigger"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         example="Triggered when a user joins a project",
 *         description="Description of the webhook event trigger"
 *     ),
 *     @OA\Property(
 *         property="enabled",
 *         type="boolean",
 *         example=true,
 *         description="Indicates whether the webhook event trigger is enabled"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the webhook event trigger was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-26T12:00:00Z",
 *         description="Timestamp when the webhook event trigger was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name
 * @property string $description
 * @property int $enabled
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustodianWebhookReceiver> $receivers
 * @property-read int|null $receivers_count
 * @method static \Database\Factories\WebhookEventTriggerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEventTrigger whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WebhookEventTrigger extends Model
{
    use HasFactory;

    public $table = 'webhook_event_triggers';

    public $timestamps = true;

    public const WEBHOOK_EVENT_TRIGGER_USER_LEFT_PROJECT = 'user-left-project';
    public const WEBHOOK_EVENT_TRIGGER_USER_JOINED_PROJECT = 'user-joined-project';
    public const WEBHOOK_EVENT_TRIGGER_REGISTRY_READ_REQUEST_ACCEPTED = 'user-accepted-read-request';
    public const WEBHOOK_EVENT_TRIGGER_REGISTRY_READ_REQUEST_REJECTED = 'user-rejected-read-request';

    public const TRIGGER_EVENT_STATUS = [
        1 => self::WEBHOOK_EVENT_TRIGGER_REGISTRY_READ_REQUEST_ACCEPTED,
        2 => self::WEBHOOK_EVENT_TRIGGER_REGISTRY_READ_REQUEST_REJECTED,
    ];

    protected $fillable = [
        'name',
        'description',
        'trigger_signature',
        'enabled',
    ];

    /**
     * Get the receivers associated with this webhook event trigger.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\CustodianWebhookReceiver>
     */
    public function receivers(): HasMany
    {
        return $this->hasMany(CustodianWebhookReceiver::class, 'webhook_event', 'id');
    }
}
