#!/bin/sh
# =============================================================
# entrypoint.sh — Arranca Nginx + PHP-FPM directamente
# Las migraciones se corren automáticamente en cada arranque (idempotente:
# Laravel solo aplica las que falten según la tabla "migrations" de cada BD).
# =============================================================
set -e

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force

echo "==> Linking public storage..."
rm -rf /var/www/html/public/storage
php /var/www/html/artisan storage:link

echo "==> Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "==> Starting supervisord (Nginx + PHP-FPM)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
