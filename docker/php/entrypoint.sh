#!/usr/bin/env bash
set -e

echo "⏳ Ждём Postgres и Redis111..."
until nc -z postgres 5432; do sleep 1; done
until nc -z redis 6379; do sleep 1; done

if [ -f composer.json ]; then
  composer install --no-interaction --prefer-dist
fi

php artisan migrate --force || true
php artisan l5-swagger:generate || true

chmod -R 777 storage bootstrap/cache || true

exec supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
