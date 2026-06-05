#!/bin/sh
# =============================================================
# entrypoint.sh — Arranca Nginx + PHP-FPM directamente
# Las migraciones se corren manualmente:
#   docker compose exec app php artisan migrate
# =============================================================
set -e

echo "==> Fixing storage permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "==> Starting supervisord (Nginx + PHP-FPM)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
