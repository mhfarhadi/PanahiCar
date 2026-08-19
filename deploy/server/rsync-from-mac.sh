#!/usr/bin/env bash
# Sync project from Mac to VPS, then rebuild on server (no git pull on server).
# Usage: bash deploy/server/rsync-from-mac.sh [user@host]
set -euo pipefail

SSH_TARGET="${1:-root@185.8.173.114}"
APP_DIR="/var/www/panahi"
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"

echo "==> Rsync Panahi Car to ${SSH_TARGET}:${APP_DIR}"

rsync -avz --delete \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude 'storage/logs/*' \
  --exclude 'storage/framework/cache/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  "${ROOT}/" "${SSH_TARGET}:${APP_DIR}/"

echo "==> Rebuild on server"
ssh "${SSH_TARGET}" "cd ${APP_DIR} && composer install --no-dev --optimize-autoloader --no-interaction && npm ci && npm run build && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && chown -R www-data:www-data storage bootstrap/cache || chown -R apache:apache storage bootstrap/cache"

echo ""
echo "Done. Test: http://185.8.173.114/panahi/"
