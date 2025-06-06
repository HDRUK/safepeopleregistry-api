<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
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
