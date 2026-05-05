#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

git config --global --add safe.directory /var/www/html || true

if [ ! -f vendor/autoload_runtime.php ]; then
    echo "[entrypoint] configuring composer mirror (aliyun)..."
    composer config -g repos.packagist composer https://mirrors.aliyun.com/composer/
    echo "[entrypoint] vendor/ is empty, running composer install..."
    for attempt in 1 2 3; do
        if composer install --prefer-dist --no-progress; then
            break
        fi
        echo "[entrypoint] composer install failed (attempt ${attempt}/3), retrying in 10s..."
        sleep 10
        if [ "${attempt}" = "3" ]; then
            echo "[entrypoint] composer install failed after 3 attempts" >&2
            exit 1
        fi
    done
    chown -R www-data:www-data vendor var
else
    echo "[entrypoint] vendor/ already populated, skipping composer install"
fi

exec "$@"
