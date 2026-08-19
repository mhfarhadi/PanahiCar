#!/usr/bin/env bash
# Fix 404 for http://HOST/panahi — wires Nginx without touching MayaHamrah routes.
set -euo pipefail

SNIPPET_SRC="${1:-/var/www/panahi/deploy/server/nginx-panahi-snippet.conf}"
SNIPPET_DST="/etc/nginx/snippets/panahi-car.conf"
APP_DIR="/var/www/panahi"

echo "==> Checking app files"
if [[ ! -f "${APP_DIR}/public/index.php" ]]; then
  echo "ERROR: ${APP_DIR}/public/index.php not found. Run rsync first."
  exit 1
fi

echo "==> Detecting PHP-FPM socket"
PHP_SOCK=""
for s in /var/run/php/php8.4-fpm.sock /var/run/php/php8.3-fpm.sock /var/run/php/php8.2-fpm.sock /var/run/php/php-fpm.sock; do
  if [[ -S "$s" ]]; then
    PHP_SOCK="$s"
    break
  fi
done
if [[ -z "${PHP_SOCK}" ]]; then
  echo "ERROR: No php-fpm socket found. Run: ls /var/run/php/"
  exit 1
fi
echo "    Using ${PHP_SOCK}"

mkdir -p /etc/nginx/snippets
sed "s|unix:/var/run/php/php8.4-fpm.sock|unix:${PHP_SOCK}|g" "${SNIPPET_SRC}" > "${SNIPPET_DST}"

# Remove broken conf.d file if it exists (location blocks without server{} break nginx)
if [[ -f /etc/nginx/conf.d/panahi-car.conf ]]; then
  rm -f /etc/nginx/conf.d/panahi-car.conf
  echo "    Removed invalid /etc/nginx/conf.d/panahi-car.conf"
fi

echo "==> Finding Nginx site config for this server"
SITE=""
for f in /etc/nginx/sites-enabled/* /etc/nginx/conf.d/*.conf; do
  [[ -f "$f" ]] || continue
  if grep -qE '185\.8\.173\.114|server_name|listen 80' "$f" 2>/dev/null; then
    SITE="$f"
    break
  fi
done
if [[ -z "${SITE}" ]]; then
  SITE="$(ls -1 /etc/nginx/sites-enabled/* 2>/dev/null | head -1 || true)"
fi
if [[ -z "${SITE}" ]]; then
  echo "ERROR: Could not find nginx site config. Add manually:"
  echo "  include ${SNIPPET_DST};"
  echo "inside server { } then: nginx -t && systemctl reload nginx"
  exit 1
fi
echo "    Site file: ${SITE}"

if ! grep -q 'snippets/panahi-car.conf' "${SITE}"; then
  # Insert include before closing brace of first server block (simple append before last })
  cp "${SITE}" "${SITE}.bak.$(date +%s)"
  awk -v inc="    include ${SNIPPET_DST};" '
    /^[[:space:]]*server[[:space:]]*\{/ { inserver=1 }
    inserver && /^[[:space:]]*\}/ && !done { print inc; done=1 }
    { print }
  ' "${SITE}" > "${SITE}.tmp" && mv "${SITE}.tmp" "${SITE}"
  echo "    Added include to ${SITE}"
else
  echo "    Include already present in ${SITE}"
fi

echo "==> Testing Nginx"
nginx -t
systemctl reload nginx

echo ""
echo "Done. Test: curl -I http://127.0.0.1/panahi/"
curl -sI "http://127.0.0.1/panahi/" | head -5
echo ""
echo "Browser: http://185.8.173.114/panahi/"
