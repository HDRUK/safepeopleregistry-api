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
