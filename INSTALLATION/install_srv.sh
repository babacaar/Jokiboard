#!/usr/bin/env bash
set -euo pipefail
exec 2>install_debug.log

# ------------------ Paramètres obligatoires ------------------
# 1  PROJECT_DIR   – chemin d’installation (ex. /var/www/jokiboard)
# 2  DB_NAME       – nom de la base MariaDB
# 3  DB_USER       – utilisateur MariaDB
# 4  DB_PASS       – mot de passe utilisateur MariaDB
# 5  DB_HOST|auto  – IP ou FQDN du serveur SQL ("auto" = IP locale)
# 6  DB_PORT       – port SQL (défaut 3306)
# 7  ADMIN_USER    – login du compte administrateur de l’app
# 8  ADMIN_PASS    – mot de passe admin
# 9  ADMIN_EMAIL   – email admin (pour vhost Apache)
# 10 DOMAIN_NAME   – nom de domaine du vhost (ex. jokiboard.local)
# -------------------------------------------------------------
if [ "$#" -ne 10 ]; then
  echo "❌  Usage : sudo $0 PROJECT_DIR DB_NAME DB_USER DB_PASS DB_HOST DB_PORT ADMIN_USER ADMIN_PASS ADMIN_EMAIL DOMAIN_NAME" >&2
  exit 1
fi

PROJECT_DIR="$1"; DB_NAME="$2"; DB_USER="$3"; DB_PASS="$4"
DB_HOST="${5:-auto}"; DB_PORT="$6"; ADMIN_USER="$7"; ADMIN_PASS="$8"
ADMIN_EMAIL="$9"; DOMAIN_NAME="${10}"

# IP locale automatique
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "auto" ]; then
  DB_HOST=$(hostname -I | awk '{print $1}')
fi

APACHE_USER="www-data"
DB_DUMP="$PROJECT_DIR/database/db.sql"
ENV_FILE="$PROJECT_DIR/config/.env"
VHOST_FILE="/etc/apache2/sites-available/${DOMAIN_NAME}.conf"
SSL_DIR="/etc/ssl/$DOMAIN_NAME"

echo "▶️  Installation serveur non‑interactive avec HTTPS…"

apt-get update -y && apt-get upgrade -y
apt-get install -y mariadb-server apache2 php php-pdo php-ssh2 php-mbstring php-mysql unzip mpv xdotool unclutter wmctrl graphicsmagick git openssl

# Crée le dossier du projet
mkdir -p "$PROJECT_DIR"

# Clone JokiBoard uniquement si index.php absent
if [ ! -f "$PROJECT_DIR/index.php" ]; then
  git clone https://github.com/babacaar/JokiBoard.git "$PROJECT_DIR"
fi

# Configuration MariaDB pour accès distant
CONF_FILE="/etc/mysql/mariadb.conf.d/50-server.cnf"
if grep -q '^bind-address' "$CONF_FILE"; then
  sed -i 's/^bind-address.*/bind-address = 0.0.0.0/' "$CONF_FILE"
else
  echo 'bind-address = 0.0.0.0' >> "$CONF_FILE"
fi
systemctl restart mariadb

# Création BDD
mysql -u root <<SQL
DROP DATABASE IF EXISTS $DB_NAME;
DROP USER IF EXISTS '$DB_USER'@'%';
CREATE DATABASE $DB_NAME;
CREATE USER '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
SQL

# Import du dump SQL
if [ ! -f "$DB_DUMP" ]; then
  echo "❌  Dump inexistant : $DB_DUMP" >&2
  exit 1
fi
mysql -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" -P "$DB_PORT" "$DB_NAME" < "$DB_DUMP"

# Admin utilisateur + rôle
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<SQL
INSERT INTO configuration (Conf_date, Conf_sites)
VALUES (NOW(), 'https://$DB_HOST/public/display_absences.php https://$DB_HOST/public/menupeda.jpg https://$DB_HOST/public/menu.jpg')
  ON DUPLICATE KEY UPDATE Conf_date = NOW();

INSERT IGNORE INTO Roles (nom_role) VALUES ('administrateur');

SET @hashed := SHA2('$ADMIN_PASS', 256);
INSERT INTO Utilisateurs (nom_utilisateur, mot_de_passe, email)
VALUES ('$ADMIN_USER', @hashed, '$ADMIN_EMAIL')
  ON DUPLICATE KEY UPDATE mot_de_passe=@hashed, email='$ADMIN_EMAIL';

SET @uid := (SELECT id FROM Utilisateurs WHERE nom_utilisateur = '$ADMIN_USER');
SET @rid := (SELECT id FROM Roles WHERE nom_role = 'administrateur');
INSERT IGNORE INTO Utilisateurs_Roles (id_utilisateur, id_role) VALUES (@uid, @rid);
SQL

# Fichier .env
mkdir -p "$(dirname "$ENV_FILE")"
cat > "$ENV_FILE" <<EOF
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASS=$DB_PASS
SITE_URL=$PROJECT_DIR
EOF

# Fichier .htaccess
cat > "$PROJECT_DIR/.htaccess" <<'EOF'
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteRule ^$ public/connexion.php [L]
  RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
  RewriteBase /
  RewriteRule ^index\.php$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.php [L]
</IfModule>
EOF

# Droits
chown -R "$APACHE_USER":"$APACHE_USER" "$PROJECT_DIR"

# Génération certificat auto-signé si domaine en .local
if [[ "$DOMAIN_NAME" == *.local ]]; then
  mkdir -p "$SSL_DIR"
  openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$SSL_DIR/privkey.pem" \
    -out "$SSL_DIR/fullchain.pem" \
    -subj "/CN=$DOMAIN_NAME"
fi

# VHost Apache HTTP + HTTPS
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

  ErrorLog \${APACHE_LOG_DIR}/${DOMAIN_NAME}_error.log
  CustomLog \${APACHE_LOG_DIR}/${DOMAIN_NAME}_access.log combined

  SSLEngine on
  SSLCertificateFile $SSL_DIR/fullchain.pem
  SSLCertificateKeyFile $SSL_DIR/privkey.pem
</VirtualHost>
EOF

# Activation Apache
a2dissite 000-default.conf >/dev/null || true
a2ensite "${DOMAIN_NAME}.conf" >/dev/null
a2enmod rewrite ssl >/dev/null
systemctl reload apache2

echo "✅  Installation terminée : https://$DOMAIN_NAME"
