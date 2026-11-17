#!/bin/bash
set -e

if [ -e /var/www/.env ]; then
    source /var/www/.env
fi

base_command="php artisan octane:frankenphp --max-requests=250 --host=0.0.0.0 --port=8100"

if [ "$APP_ENV" = 'local' ]; then
    echo 'running in dev mode - with watch'
    # base_command="$base_command --watch"

    if [ "$REBUILD_DB" = "1" ]; then
        # Completely clear down the data in local/dev envs
        php artisan migrate:fresh
        php artisan db:seed --class=BaseDemoSeeder
    else
        php artisan migrate
    fi
else
    # Only forward-facing migrations anywhere else
    php artisan migrate
    # call the email template seeder to updateOrCreate without truncating first
    DISABLE_TRUNCATE=true php artisan db:seed --class=EmailTemplatesSeeder
    php artisan validation:generate-logs

    echo "running in prod mode"
fi

# Run pending deployment steps (run-once, forward-only data fixes/seeding).
# Must run after migrations so the deployment_steps table and schema exist.
echo "Running deployment steps..."
php artisan deploy:run

# ---------------------------------------------------------------------------
# Long-running services: Octane (HTTP) + Horizon (queue workers).
#
# This script is PID 1. Contract (see docs/ARCHITECTURE/QUEUE_WORKERS.md):
#   - If EITHER service exits, stop the other and exit non-zero, so Kubernetes
#     restarts the pod. Never keep serving HTTP with no queue worker behind it.
#   - On SIGTERM/SIGINT (redeploy, scale-down), forward the signal to both so
#     Horizon can finish in-flight jobs and release its master lock cleanly.
#
# Horizon itself supervises the individual workers (restarting them on crash,
# memory limit, maxJobs, etc.) using config/horizon.php.
# ---------------------------------------------------------------------------

# From here on we handle failures explicitly rather than via `set -e`.
set +e

OCTANE_PID=""
HORIZON_PID=""

stop_services() {
    kill -TERM $OCTANE_PID $HORIZON_PID 2>/dev/null
    wait $OCTANE_PID $HORIZON_PID 2>/dev/null
}

on_signal() {
    echo "Stop signal received, shutting down Octane and Horizon..."
    stop_services
    exit 0
}

# Trap before starting anything, so an early stop signal is still forwarded.
trap on_signal TERM INT

$base_command &
OCTANE_PID=$!

php artisan horizon &
HORIZON_PID=$!

# Block until the first service exits (requires bash >= 4.3).
wait -n $OCTANE_PID $HORIZON_PID
status=$?

echo "A service exited unexpectedly (status $status); stopping the container so it is restarted."
stop_services

# Any service stopping on its own is a failure from the container's view,
# even with status 0 (e.g. `php artisan horizon:terminate`).
[ "$status" -eq 0 ] && status=1
exit "$status"
