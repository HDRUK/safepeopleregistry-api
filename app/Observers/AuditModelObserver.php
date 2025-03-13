<?php

namespace App\Observers;

use App\Services\AuditingService;
use Illuminate\Database\Eloquent\Model;

class AuditModelObserver
{
    protected $auditingService;

    public function __construct(AuditingService $auditingService)
    {
        $this->auditingService = $auditingService;
    }

    /**
     * Dynamically handle all Eloquent events
     */
    public function handle(string $event, Model $model): void
    {
        $eventType = $this->resolveEventType($event);

        if (method_exists($this, $eventType)) {
            $this->{$eventType}($model);
        }
    }

    /**
     * Map Eloquent event names like `eloquent.created: App\Models\User` -> `created`
     */
    private function resolveEventType(string $event): ?string
    {
        preg_match('/eloquent\.(\w+):/', $event, $matches);

        return $matches[1] ?? null;
    }

    public function created(Model $model): void
    {
        $this->auditingService->log('info', $this->norm([
            'action' => 'model created',
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => json_encode($model->getOriginal()),
            'new_values' => json_encode($model->getDirty()),
            'user_id' => auth()->id(),
        ]));
    }

    public function updated(Model $model): void
    {
        $this->auditingService->log('info', $this->norm([
            'action' => 'model updated',
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => json_encode($model->getOriginal()),
            'new_values' => json_encode($model->getDirty()),
            'user_id' => auth()->id(),
        ]));
    }

    public function deleted($model): void
    {
        $this->auditingService->log('info', $this->norm([
            'action' => 'model deleted',
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => json_encode($model->getOriginal()),
            'new_values' => json_encode($model->getDirty()),
            'user_id' => auth()->id(),
        ]));
    }

    private function norm(array $data): string
    {
        return json_encode($data);
    }
}
