<?php

namespace App\Exceptions;

use Throwable;
use App\Services\AuditingService;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function report(Throwable $ex): void
    {
        app(AuditingService::class)->logException($ex);
        parent::report($ex);
    }
}
