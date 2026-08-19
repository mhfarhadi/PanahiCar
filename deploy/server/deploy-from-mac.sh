#!/usr/bin/env bash
# Run ON YOUR MAC to update the VPS when SSH works locally.
# Usage: bash deploy/server/deploy-from-mac.sh [user@host]
set -euo pipefail

SSH_TARGET="${1:-root@185.8.173.114}"
APP_DIR="/var/www/panahi"

echo "==> Deploying Panahi Car to ${SSH_TARGET}"
echo "    Target: ${APP_DIR}"

ssh "${SSH_TARGET}" "bash -s" <<'REMOTE'
set -euo pipefail
APP_DIR="/var/www/panahi"

if [[ -d "${APP_DIR}/.git" ]]; then
  cd "${APP_DIR}"
  bash deploy/server/update-panahi.sh
else
  echo "No clone at ${APP_DIR}. Running full install..."
  bash -c "$(curl -fsSL https://raw.githubusercontent.com/mhfarhadi/PanahiCar/main/deploy/server/install-panahi.sh)" || {
    echo "Clone repo first, then re-run this script."
    exit 1
  }
fi
REMOTE

echo ""
echo "Deploy finished. Test: http://185.8.173.114/panahi/"
