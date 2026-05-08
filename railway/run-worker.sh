#!/bin/sh
set -eu

queue_name="${DB_QUEUE:-default}"
sleep_seconds="${QUEUE_WORKER_SLEEP:-3}"
timeout_seconds="${QUEUE_WORKER_TIMEOUT:-90}"
max_tries="${QUEUE_WORKER_TRIES:-3}"
max_time_seconds="${QUEUE_WORKER_MAX_TIME:-3600}"

exec php artisan queue:work database \
    --queue="$queue_name" \
    --sleep="$sleep_seconds" \
    --timeout="$timeout_seconds" \
    --tries="$max_tries" \
    --max-time="$max_time_seconds" \
    --no-interaction \
    -v
