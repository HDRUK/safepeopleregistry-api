<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\ActionLogType;

class ActionLog extends Model
{
    use HasFactory;

    protected $fillable = ['entity_id', 'entity_type', 'action', 'type', 'completed_at'];

    public $timestamps = false;

    protected $casts = [
        'type' => ActionLogType::class,
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
