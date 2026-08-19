#!/usr/bin/env bash
# Panahi Car — pull latest main and rebuild (existing /var/www/panahi install).
# Safe for repeat runs: does NOT recreate DB user or overwrite .env secrets.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/panahi}"
WEB_SUBPATH="${WEB_SUBPATH:-panahi}"
BRANCH="${BRANCH:-main}"

echo "==> Panahi Car update"
echo "    App dir: ${APP_DIR}"

if [[ ! -d "${APP_DIR}/.git" ]]; then
  echo "ERROR: ${APP_DIR} is not a git clone. Run install-panahi.sh first."
  exit 1
fi

cd "${APP_DIR}"

echo "==> Pulling latest ${BRANCH}"
git fetch origin "${BRANCH}"
git checkout "${BRANCH}"
git pull --ff-only origin "${BRANCH}"

echo "==> PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Frontend build"
npm ci
npm run build

HTACCESS="public/.htaccess"
if ! grep -q 'RewriteBase /' "${HTACCESS}"; then
  sed -i "s/RewriteEngine On/RewriteEngine On\n\n    RewriteBase \\/${WEB_SUBPATH}/" "${HTACCESS}"
fi

sed -i "s|\"start_url\": \"/dashboard\"|\"start_url\": \"/${WEB_SUBPATH}/dashboard\"|" public/manifest.json || true

echo "==> Migrations"
php artisan migrate --force

echo "==> Laravel caches"
php artisan storage:link --force || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache || chown -R apache:apache storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache

echo ""
echo "Done. Open: http://185.8.173.114/${WEB_SUBPATH}/"
