#!/usr/bin/env bash
# Atualiza o ELOMIAH preservando dados do admin (data/ e public/uploads/).
# Uso (dentro do container, como root): bash /opt/elomiah/deploy/update.sh
set -euo pipefail

APP_DIR="/opt/elomiah"
APP_USER="elomiah"
BACKUP_DIR="/var/backups/elomiah/$(date +%Y%m%d-%H%M%S)"

cd "$APP_DIR"
BRANCH="$(runuser -u "$APP_USER" -- git rev-parse --abbrev-ref HEAD)"

echo "==> Backup em ${BACKUP_DIR}"
mkdir -p "$BACKUP_DIR"
cp -a data "$BACKUP_DIR/"
cp -a public/uploads "$BACKUP_DIR/" 2>/dev/null || true

echo "==> Atualizando código (${BRANCH})"
runuser -u "$APP_USER" -- git fetch origin
runuser -u "$APP_USER" -- git reset --hard "origin/${BRANCH}"

echo "==> Restaurando dados"
cp -a "$BACKUP_DIR/data/." data/
[[ -d "$BACKUP_DIR/uploads" ]] && cp -a "$BACKUP_DIR/uploads/." public/uploads/
chown -R "$APP_USER:$APP_USER" "$APP_DIR"

echo "==> Build"
runuser -u "$APP_USER" -- npm ci
runuser -u "$APP_USER" -- npm run build

cp deploy/elomiah.service /etc/systemd/system/elomiah.service
systemctl daemon-reload
systemctl restart elomiah
echo "Atualizado."
