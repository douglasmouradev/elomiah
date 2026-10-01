#!/usr/bin/env bash
# Instala o ELOMIAH (PHP 8.3 + MariaDB + Nginx) num container LXC Debian 12 (Proxmox).
# Uso (dentro do container, como root):
#   APP_URL=https://elomiah.exemplo.ts.net ADMIN_PASSWORD='senha' bash setup-lxc.sh
# Variáveis opcionais: REPO_URL, BRANCH, ADMIN_EMAIL
set -euo pipefail

REPO_URL="${REPO_URL:-https://github.com/douglasmouradev/elomiah.git}"
BRANCH="${BRANCH:-main}"
APP_DIR="/opt/elomiah"
APP_URL="${APP_URL:-http://$(hostname -I | awk '{print $1}')}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@elomiah.com}"
PHP_VER="8.3"
DB_NAME="elomiah"
DB_USER="elomiah"

if [[ $EUID -ne 0 ]]; then
  echo "Execute como root." >&2
  exit 1
fi

export DEBIAN_FRONTEND=noninteractive

echo "==> Desligando a versão Next.js antiga (se existir)"
for svc in elomiah elomiah-tunnel; do
  if systemctl list-unit-files "${svc}.service" >/dev/null 2>&1 && systemctl cat "${svc}.service" >/dev/null 2>&1; then
    systemctl disable --now "${svc}.service" || true
    rm -f "/etc/systemd/system/${svc}.service"
  fi
done
systemctl daemon-reload
if [[ -f "$APP_DIR/package.json" ]]; then
  OLD_DIR="/opt/elomiah-next.bak-$(date +%Y%m%d-%H%M%S)"
  echo "   movendo ${APP_DIR} (Next.js) para ${OLD_DIR}"
  mv "$APP_DIR" "$OLD_DIR"
elif [[ -d "$APP_DIR" && ! -d "$APP_DIR/.git" ]]; then
  OLD_DIR="${APP_DIR}.bak-$(date +%Y%m%d-%H%M%S)"
  echo "   movendo ${APP_DIR} para ${OLD_DIR}"
  mv "$APP_DIR" "$OLD_DIR"
fi

echo "==> Pacotes do sistema"
apt-get update
apt-get install -y curl ca-certificates git nginx gnupg lsb-release openssl mariadb-server

if [[ ! -f /etc/apt/sources.list.d/php-sury.list ]]; then
  echo "==> Repositório PHP ${PHP_VER} (packages.sury.org)"
  curl -fsSL https://packages.sury.org/php/apt.gpg -o /usr/share/keyrings/php-sury.gpg
  echo "deb [signed-by=/usr/share/keyrings/php-sury.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
    > /etc/apt/sources.list.d/php-sury.list
  apt-get update
fi
apt-get install -y "php${PHP_VER}-fpm" "php${PHP_VER}-cli" "php${PHP_VER}-mysql" "php${PHP_VER}-mbstring" \
  "php${PHP_VER}-curl" "php${PHP_VER}-gd" "php${PHP_VER}-xml" "php${PHP_VER}-intl" "php${PHP_VER}-zip"

cat > "/etc/php/${PHP_VER}/fpm/conf.d/99-elomiah.ini" <<'EOF'
upload_max_filesize = 8M
post_max_size = 24M
memory_limit = 256M
expose_php = Off
date.timezone = America/Sao_Paulo
EOF

echo "==> Código em ${APP_DIR}"
if [[ ! -d "$APP_DIR/.git" ]]; then
  git clone --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
fi
cd "$APP_DIR"

FIRST_INSTALL=0
if [[ ! -f "$APP_DIR/.env" ]]; then
  FIRST_INSTALL=1
  DB_PASS="$(openssl rand -hex 16)"
  cp .env.example .env
  sed -i \
    -e "s|^APP_ENV=.*|APP_ENV=production|" \
    -e "s|^APP_DEBUG=.*|APP_DEBUG=false|" \
    -e "s|^APP_URL=.*|APP_URL=${APP_URL}|" \
    -e "s|^APP_KEY=.*|APP_KEY=$(openssl rand -hex 32)|" \
    -e "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" \
    -e "s|^DB_NAME=.*|DB_NAME=${DB_NAME}|" \
    -e "s|^DB_USER=.*|DB_USER=${DB_USER}|" \
    -e "s|^DB_PASS=.*|DB_PASS=${DB_PASS}|" \
    .env
  if [[ "$APP_URL" == https://* ]]; then
    sed -i "s|^SESSION_SECURE=.*|SESSION_SECURE=1|" .env
  fi
  chmod 640 .env
fi
DB_PASS="$(grep '^DB_PASS=' .env | cut -d= -f2-)"

echo "==> Banco de dados"
systemctl enable --now mariadb
mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'127.0.0.1' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

TABLES="$(mysql -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}'")"
if [[ "$TABLES" == "0" ]]; then
  echo "   banco vazio: criando tabelas e dados iniciais"
  mysql --default-character-set=utf8mb4 < database/schema.sql
  mysql --default-character-set=utf8mb4 "$DB_NAME" < database/seed.sql
else
  echo "   banco já tem ${TABLES} tabelas; mantendo os dados"
fi

if [[ -n "${ADMIN_PASSWORD:-}" ]]; then
  echo "==> Senha do admin (${ADMIN_EMAIL})"
  HASH="$(ADMIN_PASSWORD="$ADMIN_PASSWORD" php -r 'echo password_hash(getenv("ADMIN_PASSWORD"), PASSWORD_DEFAULT);')"
  mysql "$DB_NAME" -e "UPDATE usuarios SET senha_hash='${HASH}', email='${ADMIN_EMAIL}' WHERE role='admin' ORDER BY id LIMIT 1;"
fi

echo "==> Permissões"
mkdir -p storage/logs public/assets/uploads/produtos public/assets/uploads/depoimentos
chown root:www-data .env
chown -R www-data:www-data storage public/assets/uploads

echo "==> Nginx"
cp deploy/nginx-elomiah.conf /etc/nginx/sites-available/elomiah
ln -sf /etc/nginx/sites-available/elomiah /etc/nginx/sites-enabled/elomiah
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable --now "php${PHP_VER}-fpm" nginx
systemctl restart "php${PHP_VER}-fpm"
systemctl reload nginx

echo
echo "Pronto: ${APP_URL}"
echo "Admin:  ${APP_URL}/admin  (${ADMIN_EMAIL})"
if [[ $FIRST_INSTALL -eq 1 ]]; then
  echo "Configure Pix, Mercado Pago e e-mail em ${APP_DIR}/.env e rode: systemctl reload php${PHP_VER}-fpm"
fi
if [[ -z "${ADMIN_PASSWORD:-}" ]]; then
  echo "!! Senha do admin não definida. Rode de novo com ADMIN_PASSWORD='...' para definir."
fi
