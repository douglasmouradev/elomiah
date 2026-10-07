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

cat > "/etc/php/${PHP_VER}/fpm/conf.d/99-elomiah.ini" <<'INI'
upload_max_filesize = 16M
post_max_size = 64M
memory_limit = 256M
expose_php = Off
date.timezone = America/Sao_Paulo
INI

chown -R www-data:www-data storage public/assets/uploads
cp deploy/nginx-elomiah.conf /etc/nginx/sites-available/elomiah
nginx -t
systemctl reload "php${PHP_VER}-fpm" nginx
echo "Atualizado."
