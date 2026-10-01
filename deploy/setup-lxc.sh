#!/usr/bin/env bash
# Instala o ELOMIAH num container LXC Debian/Ubuntu (Proxmox).
# Uso (dentro do container, como root):
#   bash setup-lxc.sh [URL_DO_REPO] [BRANCH]
set -euo pipefail

REPO_URL="${1:-https://github.com/douglasmouradev/elomiah.git}"
BRANCH="${2:-main}"
APP_DIR="/opt/elomiah"
APP_USER="elomiah"
NODE_MAJOR="20"

if [[ $EUID -ne 0 ]]; then
  echo "Execute como root." >&2
  exit 1
fi

echo "==> Pacotes do sistema"
apt-get update
apt-get install -y curl ca-certificates git nginx gnupg

if ! command -v node >/dev/null || [[ "$(node -v | cut -d. -f1 | tr -d v)" -lt 18 ]]; then
  echo "==> Node.js ${NODE_MAJOR}"
  curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | bash -
  apt-get install -y nodejs
fi

echo "==> Usuário ${APP_USER}"
id "$APP_USER" >/dev/null 2>&1 || useradd --system --create-home --shell /usr/sbin/nologin "$APP_USER"

echo "==> Código em ${APP_DIR}"
if [[ ! -d "$APP_DIR/.git" ]]; then
  git clone --branch "$BRANCH" "$REPO_URL" "$APP_DIR"
fi
mkdir -p "$APP_DIR/public/uploads" "$APP_DIR/data"

if [[ ! -f "$APP_DIR/.env.production.local" ]]; then
  cp "$APP_DIR/.env.example" "$APP_DIR/.env.production.local"
  SECRET="$(openssl rand -hex 32 2>/dev/null || head -c 32 /dev/urandom | od -An -tx1 | tr -d ' \n')"
  sed -i "s|^ADMIN_SECRET=.*|ADMIN_SECRET=${SECRET}|" "$APP_DIR/.env.production.local"
  echo
  echo "!! Edite ${APP_DIR}/.env.production.local e defina ADMIN_PASSWORD (e o resto) antes de usar o admin."
fi

chown -R "$APP_USER:$APP_USER" "$APP_DIR"

echo "==> Build"
sudo_app() { runuser -u "$APP_USER" -- "$@"; }
cd "$APP_DIR"
sudo_app npm ci
sudo_app npm run build

echo "==> systemd"
cp "$APP_DIR/deploy/elomiah.service" /etc/systemd/system/elomiah.service
systemctl daemon-reload
systemctl enable --now elomiah

echo "==> Nginx"
cp "$APP_DIR/deploy/nginx-elomiah.conf" /etc/nginx/sites-available/elomiah
ln -sf /etc/nginx/sites-available/elomiah /etc/nginx/sites-enabled/elomiah
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

IP="$(hostname -I | awk '{print $1}')"
echo
echo "Pronto: http://${IP}"
