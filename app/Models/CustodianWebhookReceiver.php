<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function eventTrigger(): BelongsTo
    {
        return $this->belongsTo(WebhookEventTrigger::class, 'webhook_event', 'id');
    }
}
