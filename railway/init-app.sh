#!/bin/sh
set -eu

mode="${1:-}"

case "$mode" in
    --migrate-only)
        php artisan migrate --force
        ;;
    --runtime-only)
        mkdir -p \
            bootstrap/cache \
            storage/app/public \
            storage/framework/cache/data \
            storage/framework/sessions \
            storage/framework/testing \
            storage/framework/views \
            storage/logs

        chmod -R ug+rwX bootstrap/cache storage
        chown -R www-data:www-data bootstrap/cache storage 2>/dev/null || true

        if [ ! -L public/storage ] && [ ! -e public/storage ]; then
            php artisan storage:link
        fi

        php artisan config:clear
        php artisan view:clear
        php artisan config:cache
        php artisan view:cache
        ;;
    *)
        echo "Usage: sh railway/init-app.sh --migrate-only|--runtime-only" >&2
        exit 1
        ;;
esac
