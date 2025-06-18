# ============================================================================
# install_server_no_dialog.sh  (HTTPS auto‑signé pour domaine local)
# ============================================================================
#!/usr/bin/env bash
set -euo pipefail
exec 2>install_debug.log

# ------------------ Paramètres ------------------
if [ "$#" -ne 10 ]; then
  echo "❌  Usage : sudo $0 PROJECT_DIR DB_NAME DB_USER DB_PASS DB_HOST DB_PORT ADMIN_USER ADMIN_PASS ADMIN_EMAIL DOMAIN_NAME" >&2; exit 1; fi

PROJECT_DIR="$1"; DB_NAME="$2"; DB_USER="$3"; DB_PASS="$4"
DB_HOST="${5:-auto}"; DB_PORT="$6"; ADMIN_USER="$7"; ADMIN_PASS="$8"
ADMIN_EMAIL="$9"; DOMAIN_NAME="${10}"

# IP locale si auto
[ "$DB_HOST" = "auto" ] && DB_HOST=$(hostname -I | awk '{print $1}')

APACHE_USER="www-data"
DB_DUMP="$PROJECT_DIR/database/db.sql"
ENV_FILE="$PROJECT_DIR/config/.env"
VHOST_FILE="/etc/apache2/sites-available/${DOMAIN_NAME}.conf"

# ----------- Pré‑requis système -----------
apt-get update -y && apt-get install -y mariadb-server apache2 php php-mysql php-mbstring php-pdo php-ssh2 unzip git openssl

# Dossier projet & clone
mkdir -p "$PROJECT_DIR"
[ ! -f "$PROJECT_DIR/index.php" ] && git clone https://github.com/babacaar/JokiBoard.git "$PROJECT_DIR"

# ----------- MariaDB -----------
CONF_FILE="/etc/mysql/mariadb.conf.d/50-server.cnf"
if grep -q '^bind-address' "$CONF_FILE"; then sed -i 's/^bind-address.*/bind-address = 0.0.0.0/' "$CONF_FILE"; else echo 'bind-address = 0.0.0.0' >> "$CONF_FILE"; fi
systemctl restart mariadb

mysql -u root <<SQL
DROP DATABASE IF EXISTS $DB_NAME;
DROP USER IF EXISTS '$DB_USER'@'%';
CREATE DATABASE $DB_NAME;
CREATE USER '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
SQL

[ -f "$DB_DUMP" ] || { echo "Dump SQL manquant ($DB_DUMP)" >&2; exit 1; }
mysql -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" -P "$DB_PORT" "$DB_NAME" < "$DB_DUMP"

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<SQL
INSERT IGNORE INTO Roles (nom_role) VALUES ('administrateur');
SET @hash := SHA2('$ADMIN_PASS',256);
INSERT INTO Utilisateurs (nom_utilisateur,mot_de_passe,email) VALUES ('$ADMIN_USER',@hash,'$ADMIN_EMAIL') ON DUPLICATE KEY UPDATE mot_de_passe=@hash,email='$ADMIN_EMAIL';
SET @uid := (SELECT id FROM Utilisateurs WHERE nom_utilisateur='$ADMIN_USER');
SET @rid := (SELECT id FROM Roles WHERE nom_role='administrateur');
INSERT IGNORE INTO Utilisateurs_Roles (id_utilisateur,id_role) VALUES (@uid,@rid);
SQL

# ----------- .env -----------
mkdir -p "$(dirname "$ENV_FILE")"
cat > "$ENV_FILE" <<EOF
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASS=$DB_PASS
SITE_URL=$PROJECT_DIR
EOF

# ----------- HTTPS : auto‑signé pour .local/.lan sinon Certbot -----------
if [[ "$DOMAIN_NAME" =~ \.(local|lan)$ ]]; then
  SSL_DIR="/etc/ssl/$DOMAIN_NAME"
  mkdir -p "$SSL_DIR"
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$SSL_DIR/privkey.pem" -out "$SSL_DIR/fullchain.pem" \
    -subj "/CN=$DOMAIN_NAME"
else
  apt-get install -y certbot python3-certbot-apache
  certbot certonly --apache -d "$DOMAIN_NAME" --non-interactive --agree-tos --email "$ADMIN_EMAIL"
  SSL_DIR="/etc/letsencrypt/live/$DOMAIN_NAME"
fi

# ----------- VHost -----------
cat > "$VHOST_FILE" <<EOF
<VirtualHost *:80>
  ServerName $DOMAIN_NAME
  Redirect permanent / https://$DOMAIN_NAME/
</VirtualHost>

<VirtualHost *:443>
  ServerAdmin $ADMIN_EMAIL
  ServerName $DOMAIN_NAME
  DocumentRoot $PROJECT_DIR

  <Directory $PROJECT_DIR>
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>

  SSLEngine on
  SSLCertificateFile $SSL_DIR/fullchain.pem
  SSLCertificateKeyFile $SSL_DIR/privkey.pem

  ErrorLog \${APACHE_LOG_DIR}/${DOMAIN_NAME}_error.log
  CustomLog \${APACHE_LOG_DIR}/${DOMAIN_NAME}_access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf >/dev/null || true
ln -sf "$VHOST_FILE" /etc/apache2/sites-enabled/
a2enmod rewrite ssl >/dev/null
systemctl reload apache2

echo "✅  Installation terminée : https://$DOMAIN_NAME"
