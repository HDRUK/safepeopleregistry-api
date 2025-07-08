#!/bin/bash
set -e

echo "[STATUS-MONITOR] Starting status monitoring (no supervisor socket required)..."

check_service() {
    local service_name=$1
    local process_pattern=$2
    
    if pgrep -f "$process_pattern" >/dev/null 2>&1; then
        local pid=$(pgrep -f "$process_pattern" | head -1)
        echo "  ✓ $service_name: RUNNING (PID: $pid)"
        return 0
    else
        echo "  ✗ $service_name: STOPPED"
        return 1
    fi
}

while true; do
    TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$TIMESTAMP] [STATUS] === Laravel Services Status ==="
    
    # Check each service
    check_service "Octane" "octane:start"
    check_service "Horizon" "artisan horizon"
    check_service "Scheduler" "schedule:run"
    
    # Show resource usage
    if [ -f /proc/meminfo ]; then
        TOTAL_MEM=$(grep MemTotal /proc/meminfo | awk '{print int($2/1024)}')
        AVAIL_MEM=$(grep MemAvailable /proc/meminfo | awk '{print int($2/1024)}')
        USED_MEM=$((TOTAL_MEM - AVAIL_MEM))
        echo "  Memory: ${USED_MEM}MB / ${TOTAL_MEM}MB used"
    fi
    
    if [ -f /proc/loadavg ]; then
        LOAD=$(cut -d' ' -f1 /proc/loadavg)
        echo "  Load: $LOAD"
    fi
    
    echo ""
    sleep 60
done