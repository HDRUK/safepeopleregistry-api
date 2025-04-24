<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function receivers(): HasMany
    {
        return $this->hasMany(CustodianWebhookReceiver::class, 'webhook_event', 'id');
    }
}
