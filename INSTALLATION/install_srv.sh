#!/bin/bash
set -euo pipefail
exec 2>install_debug.log

#./install_server.sh <project_dir> <db_name> <db_user> <db_pass> <db_host|auto> <db_port> <admin_user> <admin_pass> <admin_email> <domain_name>

# === Parametres ===
PROJECT_DIR="$1"
DB_NAME="$2"
DB_USER="$3"
DB_PASS="$4"
DB_HOST="${5:-auto}"
DB_PORT="${6:-3306}"
ADMIN_USER="$7"
ADMIN_PASS="$8"
ADMIN_EMAIL="$9"
DOMAIN_NAME="${10:-$(basename "$PROJECT_DIR").local}"

REPO_URL="https://github.com/babacaar/JokiBoard.git"
INSTALL_DIR="$PROJECT_DIR/INSTALLATION"
APACHE_USER="www-data"
DB_DUMP="$PROJECT_DIR/database/db.sql"
ENV_FILE="$PROJECT_DIR/config/.env"

# === IP locale si DB_HOST auto ===
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "auto" ]; then
  DB_HOST=$(hostname -I | awk '{print $1}')
fi

# === MAJ et paquets ===
sudo apt update -y && sudo apt upgrade -y
sudo apt install -y mariadb-server apache2 php php-pdo php-ssh2 php-mbstring php-mysql unzip mpv xdotool unclutter wmctrl graphicsmagick

# === MariaDB conf pour reseau ===
CONF_FILE="/etc/mysql/mariadb.conf.d/50-server.cnf"
sudo sed -i "s/^bind-address.*/bind-address = 0.0.0.0/" "$CONF_FILE" || echo "bind-address = 0.0.0.0" | sudo tee -a "$CONF_FILE"
sudo systemctl restart mariadb

# === .env ===
cat <<EOF > "$ENV_FILE"
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASS=$DB_PASS
SITE_URL=$PROJECT_DIR
EOF

# === Suppression base/utilisateur ===
mysql -u root <<EOF
DROP DATABASE IF EXISTS $DB_NAME;
DROP USER IF EXISTS '$DB_USER'@'%';
FLUSH PRIVILEGES;
EOF

# === Creation BDD ===
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME;
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
EOF

# === Import ===
if [ ! -f "$DB_DUMP" ]; then
  echo "Fichier SQL manquant: $DB_DUMP" >&2
  exit 1
fi

mysql -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" "$DB_NAME" < "$DB_DUMP"

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
INSERT INTO configuration (Conf_date, Conf_sites)
VALUES (NOW(), 'http://$DB_HOST/public/display_absences.php http://$DB_HOST/public/menupeda.jpg http://$DB_HOST/public/menu.jpg');
EOF

ROLE_EXISTS=$(mysql -u "$DB_USER" -p"$DB_PASS" -Nse "SELECT COUNT(*) FROM Roles WHERE nom_role = 'administrateur';" "$DB_NAME")
if [ "$ROLE_EXISTS" -eq 0 ]; then
  echo "Role 'administrateur' manquant." >&2
  exit 1
fi

HASHED_PASS=$(php -r "echo password_hash('$ADMIN_PASS', PASSWORD_DEFAULT);")

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF
INSERT INTO Utilisateurs (nom_utilisateur, mot_de_passe, email) VALUES ('$ADMIN_USER', '$HASHED_PASS', '$ADMIN_EMAIL');
SET @uid = LAST_INSERT_ID();
SET @admin_role_id = (SELECT id FROM Roles WHERE nom_role = 'administrateur' LIMIT 1);
INSERT INTO Utilisateurs_Roles (id_utilisateur, id_role) VALUES (@uid, @admin_role_id);
EOF

# === .htaccess ===
cat <<EOF > "$PROJECT_DIR/.htaccess"
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^$ public/connexion.php [L]
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteBase /
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
</IfModule>
EOF

# === Droits ===
sudo chown -R $APACHE_USER:$APACHE_USER "$PROJECT_DIR"

# === Virtual Host ===
VHOST_FILE="/etc/apache2/sites-available/${DOMAIN_NAME}.conf"
sudo bash -c "cat > $VHOST_FILE" <<EOF
<VirtualHost *:80>
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
</VirtualHost>
EOF

sudo a2dissite 000-default.conf
sudo a2ensite "${DOMAIN_NAME}.conf"
sudo a2enmod rewrite
sudo systemctl reload apache2

# ===================== HTTPS avec Let's Encrypt ======================
echo "🔐  Configuration SSL via Let's Encrypt…"

apt-get install -y certbot python3-certbot-apache

# Activation HTTPS avec redirection automatique HTTP -> HTTPS
certbot --apache --non-interactive --agree-tos --redirect \
  --email "$ADMIN_EMAIL" \
  -d "$DOMAIN_NAME"

# Vérification
if certbot certificates | grep -q "$DOMAIN_NAME"; then
  echo "✅  Certificat SSL installé pour $DOMAIN_NAME"
else
  echo "❌  Échec de l'installation du certificat SSL pour $DOMAIN_NAME" >&2
fi


# === Fin ===
echo "Installation terminee. Site dispo sur : http://$DB_HOST/ (https://$DOMAIN_NAME/)"
