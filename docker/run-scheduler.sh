#!/bin/bash
while true; do
    TIMESTAMP=$(date +'%Y-%m-%d %H:%M:%S')
    PID=$$
    echo "[SCHEDULER] $TIMESTAMP: Starting schedule:run (PID: $PID)"
    php artisan schedule:run --verbose --no-interaction
    echo "[SCHEDULER] $TIMESTAMP: Completed schedule:run (PID: $PID)"
    sleep 60
done