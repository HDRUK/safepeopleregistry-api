#!/bin/bash
set -e

echo "[STATUS-MONITOR] Starting status monitoring..."

while true; do
    TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$TIMESTAMP] [STATUS] === Process Status ==="
    
    # Check if supervisord is running
    if pgrep supervisord >/dev/null 2>&1; then
        echo "  ✓ supervisord is running (PID: $(pgrep supervisord))"
    else
        echo "  ✗ supervisord is not running"
    fi
    
    # Check Laravel processes
    if pgrep -f "octane:start" >/dev/null 2>&1; then
        echo "  ✓ Laravel Octane is running (PID: $(pgrep -f 'octane:start'))"
    else
        echo "  ✗ Laravel Octane is not running"
    fi
    
    if pgrep -f "horizon" >/dev/null 2>&1; then
        echo "  ✓ Laravel Horizon is running (PID: $(pgrep -f 'horizon'))"
    else
        echo "  ✗ Laravel Horizon is not running"
    fi
    
    if pgrep -f "schedule:run" >/dev/null 2>&1; then
        echo "  ✓ Laravel Scheduler is running (PID: $(pgrep -f 'schedule:run'))"
    else
        echo "  ✗ Laravel Scheduler is not running"
    fi
    
    # Show total process count
    TOTAL_PROCESSES=$(ps aux | wc -l)
    echo "  Total processes: $TOTAL_PROCESSES"
    
    # Try supervisorctl if available
    if command -v supervisorctl >/dev/null 2>&1; then
        echo "  Supervisor status:"
        supervisorctl -c /etc/supervisor/supervisord.conf status 2>/dev/null | sed 's/^/    /' || echo "    Unable to get supervisor status"
    fi
    
    echo ""
    sleep 60
done