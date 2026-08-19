#!/usr/bin/env bash
# Panahi Car — isolated server install at http://HOST/panahi
# Does NOT modify MayaHamrah, existing databases, or other apps.
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/panahi}"
WEB_SUBPATH="${WEB_SUBPATH:-panahi}"
APP_URL="${APP_URL:-http://185.8.173.114/${WEB_SUBPATH}}"
DB_NAME="${DB_NAME:-panahi_car}"
DB_USER="${DB_USER:-panahi_car}"
REPO_URL="${REPO_URL:-https://github.com/mhfarhadi/PanahiCar.git}"
BRANCH="${BRANCH:-main}"

echo "==> Panahi Car deploy (isolated)"
echo "    App dir:   ${APP_DIR}"
echo "    URL:       ${APP_URL}"
echo "    Database:  ${DB_NAME} (new, separate DB only)"

if [[ -d "${APP_DIR}/.git" ]]; then
  echo "==> Updating existing clone"
  git -C "${APP_DIR}" fetch origin "${BRANCH}"
  git -C "${APP_DIR}" checkout "${BRANCH}"
  git -C "${APP_DIR}" pull --ff-only origin "${BRANCH}"
else
  echo "==> Cloning repository"
  mkdir -p "$(dirname "${APP_DIR}")"
  if [[ -n "${GITHUB_TOKEN:-}" ]]; then
    git clone --branch "${BRANCH}" "https://${GITHUB_TOKEN}@github.com/mhfarhadi/PanahiCar.git" "${APP_DIR}"
  else
    git clone --branch "${BRANCH}" "${REPO_URL}" "${APP_DIR}"
  fi
fi

cd "${APP_DIR}"

if [[ ! -f .env ]]; then
  echo "==> Creating .env"
  cp .env.example .env
  php artisan key:generate --force
fi

DB_PASS="${DB_PASS:-$(openssl rand -base64 24 | tr -d '/+=' | head -c 24)}"

echo "==> Creating MySQL database and user (if missing)"
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "==> Writing production .env values"
php -r "
\$env = file_get_contents('.env');
\$map = [
  'APP_NAME' => '\"Panahi Car\"',
  'APP_ENV' => 'production',
  'APP_DEBUG' => 'false',
  'APP_URL' => '${APP_URL}',
  'DB_DATABASE' => '${DB_NAME}',
  'DB_USERNAME' => '${DB_USER}',
  'DB_PASSWORD' => '${DB_PASS}',
  'SESSION_DRIVER' => 'database',
  'CACHE_STORE' => 'database',
  'QUEUE_CONNECTION' => 'database',
];
foreach (\$map as \$k => \$v) {
  if (preg_match('/^' . preg_quote(\$k, '/') . '=/m', \$env)) {
    \$env = preg_replace('/^' . preg_quote(\$k, '/') . '=.*/m', \$k . '=' . \$v, \$env);
  } else {
    \$env .= PHP_EOL . \$k . '=' . \$v;
  }
}
file_put_contents('.env', \$env);
"

echo "==> Installing PHP dependencies"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Building frontend for subfolder /${WEB_SUBPATH}"
export VITE_BASE_PATH="/${WEB_SUBPATH}/"
npm ci
npm run build

echo "==> Subfolder rewrite base"
HTACCESS="public/.htaccess"
if ! grep -q 'RewriteBase /' "${HTACCESS}"; then
  sed -i "s/RewriteEngine On/RewriteEngine On\n\n    RewriteBase \\/${WEB_SUBPATH}/" "${HTACCESS}"
fi

sed -i "s|\"start_url\": \"/dashboard\"|\"start_url\": \"/${WEB_SUBPATH}/dashboard\"|" public/manifest.json || true

echo "==> Running migrations"
php artisan migrate --force

echo "==> Optimizing Laravel"
php artisan storage:link --force || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache || chown -R apache:apache storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache

echo "==> Linking web subfolder (symlink, does not touch existing site files except one link)"
DOCROOT=""
for candidate in /var/www/html /var/www/public /usr/share/nginx/html; do
  if [[ -d "${candidate}" ]]; then
    DOCROOT="${candidate}"
    break
  fi
done

if [[ -z "${DOCROOT}" ]]; then
  echo "WARN: Could not detect document root. Add Apache/Nginx config manually (see deploy/server/apache-panahi.conf)."
else
  LINK_PATH="${DOCROOT}/${WEB_SUBPATH}"
  if [[ -e "${LINK_PATH}" && ! -L "${LINK_PATH}" ]]; then
    echo "ERROR: ${LINK_PATH} exists and is not a symlink. Stop here to avoid overwriting."
    exit 1
  fi
  ln -sfn "${APP_DIR}/public" "${LINK_PATH}"
  echo "    Symlink: ${LINK_PATH} -> ${APP_DIR}/public"
fi

echo ""
echo "Done. Open: ${APP_URL}"
echo "DB: ${DB_NAME} / user: ${DB_USER}"
echo "If CSS/JS 404, confirm ${DOCROOT:-docroot}/${WEB_SUBPATH} points to ${APP_DIR}/public"
