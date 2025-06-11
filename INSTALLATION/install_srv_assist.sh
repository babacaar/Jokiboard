#!/bin/bash
set -euo pipefail
exec 2>install_debug.log

# === Vérification des paramètres ===
PROJECT_DIR="${1:-}"
if [ -z "$PROJECT_DIR" ]; then
    dialog --msgbox "❌ Erreur : aucun chemin de projet spécifié en argument." 6 60
    exit 1
fi

REPO_URL="https://github.com/babacaar/JokiBoard.git"
INSTALL_DIR="$PROJECT_DIR/INSTALLATION"
APACHE_USER="www-data"
DB_DUMP="$PROJECT_DIR/database/db.sql"
ENV_FILE="$PROJECT_DIR/config/.env"

# === Fenêtre de bienvenue ===
dialog --title "Installation SERVEUR" --msgbox "Bienvenue dans l'assistant d'installation SERVEUR.\n\nAppuie sur OK pour commencer." 10 60

# === Mise à jour système et installation des paquets ===
dialog --infobox "Mise à jour du système..." 5 40
sudo apt update -y && sudo apt upgrade -y

dialog --infobox "Installation des paquets requis..." 5 60
sudo apt install -y mariadb-server apache2 php php-pdo php-ssh2 php-mbstring php-mysql unzip mpv xdotool unclutter wmctrl graphicsmagick dialog

# === Config MariaDB pour accès réseau ===
CONF_FILE="/etc/mysql/mariadb.conf.d/50-server.cnf"
sudo sed -i "s/^bind-address.*/bind-address = 0.0.0.0/" "$CONF_FILE" || echo "bind-address = 0.0.0.0" | sudo tee -a "$CONF_FILE" > /dev/null
sudo systemctl restart mariadb

# === Saisie des infos de BDD ===
dialog --inputbox "Nom de la base de données :" 8 60 2>db_name.txt
DB_NAME=$(<db_name.txt)

dialog --inputbox "Utilisateur MySQL :" 8 60 2>db_user.txt
DB_USER=$(<db_user.txt)

dialog --insecure --passwordbox "Mot de passe MySQL :" 8 60 2>db_pass.txt
DB_PASS=$(<db_pass.txt)

dialog --inputbox "Hôte MySQL [IP du serveur] :" 8 60 2>db_host.txt
DB_HOST=$(<db_host.txt)

dialog --inputbox "Port MySQL [3306] :" 8 60 "3306" 2>db_port.txt
DB_PORT=$(<db_port.txt)

# === Admin ===
dialog --inputbox "Nom d'utilisateur du compte d'administrateur du site :" 8 60 "admin" 2>admin_user.txt
ADMIN_USER=$(<admin_user.txt)

dialog --insecure --passwordbox "Mot de passe du compte d'administrateur du site :" 8 60 2>admin_pass.txt
ADMIN_PASS=$(<admin_pass.txt)

dialog --inputbox "Email du compte d'administrateur du site :" 8 60 "admin@example.com" 2>admin_email.txt
ADMIN_EMAIL=$(<admin_email.txt)

# === Confirmation ===
dialog --yesno "Confirmer ces informations :\n\nDB_NAME: $DB_NAME\nDB_USER: $DB_USER\nDB_HOST: $DB_HOST\nDB_PORT: $DB_PORT" 12 60
[ $? -ne 0 ] && dialog --msgbox "Installation annulée." 6 40 && exit 1

# === Fichier .env ===
cat <<EOF > "$ENV_FILE"
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_NAME=$DB_NAME
DB_USER=$DB_USER
DB_PASS=$DB_PASS
SITE_URL=$PROJECT_DIR
EOF

# === Option de suppression base/utilisateur ===
dialog --yesno "Souhaites-tu supprimer la base de données et l'utilisateur s'ils existent ?" 8 60
if [ $? -eq 0 ]; then
    mysql -u root <<EOF
DROP DATABASE IF EXISTS $DB_NAME;
DROP USER IF EXISTS '$DB_USER'@'%';
FLUSH PRIVILEGES;
EOF
    dialog --msgbox "✅ Base de données et utilisateur supprimés." 6 50
fi

# === Création BDD et utilisateur ===
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME;
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
EOF

# === Import BDD ===
if [ ! -f "$DB_DUMP" ]; then
    dialog --msgbox "❌ Fichier de base de données manquant : $DB_DUMP" 7 60
    exit 1
fi

mysql -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" "$DB_NAME" < "$DB_DUMP" 2>mysql_err.txt || {
    dialog --title "Erreur MySQL" --textbox mysql_err.txt 15 60
    exit 1
}

# === Vérification du rôle administrateur ===
ROLE_EXISTS=$(mysql -u "$DB_USER" -p"$DB_PASS" -Nse "SELECT COUNT(*) FROM Roles WHERE nom_role = 'administrateur';" "$DB_NAME")
if [ "$ROLE_EXISTS" -eq 0 ]; then
    dialog --msgbox "❌ Le rôle 'administrateur' est introuvable dans la base." 7 60
    exit 1
fi

# === Création utilisateur admin ===
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
    #RewriteCond %{REQUEST_URI} ^/?$
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
DEFAULT_DOMAIN=$(basename "$PROJECT_DIR")
dialog --inputbox "Nom de domaine (ex: monsite.fr) :" 8 60 "$DEFAULT_DOMAIN.local" 2>domain.txt
DOMAIN_NAME=$(<domain.txt)
VHOST_FILE="/etc/apache2/sites-available/${DOMAIN_NAME}.conf"

sudo bash -c "cat > $VHOST_FILE" <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
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

# === Fin ===
dialog --title "✅ Installation terminée" --msgbox "Tu peux accéder au site via :\n\nhttp://$DB_HOST\nNom de domaine : $DOMAIN_NAME" 10 60

# === Nettoyage ===
rm -f db_name.txt db_user.txt db_pass.txt db_host.txt db_port.txt mysql_err.txt admin_user.txt admin_pass.txt admin_email.txt domain.txt
clear
