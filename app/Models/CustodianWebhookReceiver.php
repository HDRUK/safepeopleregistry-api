<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="CustodianWebhookReceiver",
 *     type="object",
 *     title="CustodianWebhookReceiver",
 *     description="Model representing webhook receivers for custodians",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="Unique identifier for the webhook receiver"
 *     ),
 *     @OA\Property(
 *         property="custodian_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the custodian associated with the webhook receiver"
 *     ),
 *     @OA\Property(
 *         property="url",
 *         type="string",
 *         example="https://example.com/webhook",
 *         description="URL of the webhook receiver"
 *     ),
 *     @OA\Property(
 *         property="webhook_event",
 *         type="integer",
 *         example=2,
 *         description="ID of the webhook event associated with the receiver"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the webhook receiver was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2025-06-25T12:00:00Z",
 *         description="Timestamp when the webhook receiver was last updated"
 *     )
 * )
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $custodian_id
 * @property string $url
 * @property int $webhook_event
 * @property-read \App\Models\WebhookEventTrigger|null $eventTrigger
 * @method static \Database\Factories\CustodianWebhookReceiverFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereCustodianId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustodianWebhookReceiver whereWebhookEvent($value)
 * @mixin \Eloquent
 */
class CustodianWebhookReceiver extends Model
{
    use HasFactory;

    public $table = 'custodian_webhook_receivers';

    public $timestamps = true;

    protected $fillable = [
        'custodian_id',
        'url',
        'webhook_event',
    ];

    /**
     *  @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\WebhookEventTrigger>
     */
    public function eventTrigger(): BelongsTo
    {
        return $this->belongsTo(WebhookEventTrigger::class, 'webhook_event', 'id');
    }
}
