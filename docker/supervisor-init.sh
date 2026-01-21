#!/bin/bash

# Log supervisor initialization
php /var/www/artisan tinker --execute="
    Log::info('Supervisor initialized', [
        'environment' => config('app.env'),
        'timestamp' => now(),
        'hostname' => gethostname(),
        'php_version' => PHP_VERSION,
    ]);
"

echo "Supervisor initialization logged successfully"

# Exit after logging (autorestart=false ensures it runs only once)
exit 0