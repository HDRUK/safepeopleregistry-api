#!/bin/bash
# /var/www/docker/resource-monitor.sh

set -e
trap '' PIPE

echo "[RESOURCE-MONITOR] Starting resource monitoring..."

while true; do
    # Memory from /proc/meminfo
    if [ -f /proc/meminfo ]; then
        TOTAL_MEM=$(grep MemTotal /proc/meminfo | awk '{print $2}')
        AVAIL_MEM=$(grep MemAvailable /proc/meminfo | awk '{print $2}')
        USED_MEM=$((TOTAL_MEM - AVAIL_MEM))
        MEMORY_PERCENT=$(awk "BEGIN {printf \"%.1f\", $USED_MEM/$TOTAL_MEM*100}")
        MEMORY_INFO="${MEMORY_PERCENT}% ($(($USED_MEM/1024))MB/$(($TOTAL_MEM/1024))MB)"
    else
        MEMORY_INFO="N/A"
    fi
    
    # CPU from /proc/loadavg
    if [ -f /proc/loadavg ]; then
        LOAD_AVG=$(cut -d' ' -f1 /proc/loadavg)
        CPU_INFO="Load: $LOAD_AVG"
    else
        CPU_INFO="N/A"
    fi
    
    TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$TIMESTAMP] [RESOURCE] Memory: $MEMORY_INFO CPU: $CPU_INFO"
    
    sleep 30
done