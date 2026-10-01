#!/usr/bin/env bash
# Atualiza o código do ELOMIAH. Banco, .env e uploads não são tocados.
# Uso (dentro do container, como root): bash /opt/elomiah/deploy/update.sh
set -euo pipefail

APP_DIR="/opt/elomiah"
PHP_VER="8.3"

cd "$APP_DIR"
BRANCH="$(git rev-parse --abbrev-ref HEAD)"

echo "==> Atualizando código (${BRANCH})"
git fetch origin
git reset --hard "origin/${BRANCH}"

chown -R www-data:www-data storage public/assets/uploads
cp deploy/nginx-elomiah.conf /etc/nginx/sites-available/elomiah
nginx -t
systemctl reload "php${PHP_VER}-fpm" nginx
echo "Atualizado."
