<?php

namespace App\Services;

use Throwable;
use App\Logging\PubSubLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Psr\Log\LoggerInterface;

class AuditingService
{
    protected $logger;

    public function log(string $level, string $message): void
    {
        // Dynamically resolve the logger based on level
        $logger = $this->resolveLogger($level);

        switch ($level) {
            case 'info':
                $logger->info($message);
                break;
            case 'error':
            case 'critical':
                $logger->error($message);
                break;
            default:
                $logger->debug($message);
                break;
        }
    }

    public function resolveLogger(string $level): LoggerInterface
    {
        if (in_array($level, ['error', 'critical'])) {
            return new PubSubLogger();
        }

        return Log::channel('stdout');
    }

    public function logModelChanges(Model $model, string $action, array $changes, string $logLevel = 'info'): void
    {
        $message = $this->generateModelMessage($model, $action, $changes);
        $this->log($logLevel, $message);
    }

    public function generateModelMessage(Model $model, string $action, array $changes): string
    {
        return 'Model: ' . get_class($model) . ', Action: ' . $action . ', Changes: ' . json_encode($changes);
    }

    public function logException(Throwable $exception): void
    {
        $this->log('error', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
        ]);
    }
}
