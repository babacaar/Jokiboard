#!/bin/bash

echo "🔧 Démarrage de l'installation de l'affichage dynamique..."

# Variables à personnaliser si besoin
GIT_REPO="https://github.com/babacaar/Projet_Raspberry.git"
CLONE_DIR="$HOME/Projet_Raspberry"
PROJECT_DIR="/var/www/monsite.fr"
DB_NAME="affichage"
DB_USER="root"
DB_PASS="ROOT"
DB_HOST="localhost"
DB_PORT="3306"

# Mise à jour des paquets
echo "📦 Mise à jour des paquets..."
sudo apt update && sudo apt upgrade -y

# Installation des dépendances
echo "⚙️ Installation de Apache, PHP, mpv et autres outils nécessaires..."
sudo apt install -y apache2 php php-pdo php-ssh2 php-ftp php-mbstring mpv git mysql-client unzip xdotool unclutter wmctrl

# Clonage du dépôt
if [ -d "$CLONE_DIR" ]; then
    echo "📁 Le dossier $CLONE_DIR existe déjà. Suppression..."
    rm -rf "$CLONE_DIR"
fi

echo "📥 Clonage du dépôt depuis $GIT_REPO..."
git clone "$GIT_REPO" "$CLONE_DIR"

# Déplacement du projet
if [ -d "$PROJECT_DIR" ]; then
    echo "⚠️ Le dossier $PROJECT_DIR existe déjà. Suppression..."
    sudo rm -rf "$PROJECT_DIR"
fi

echo "🚚 Déplacement du projet vers $PROJECT_DIR..."
sudo mv "$CLONE_DIR" "$PROJECT_DIR"

# Activer et démarrer Apache
sudo systemctl enable apache2
sudo systemctl start apache2

# Création du fichier .env
if [ ! -f "$PROJECT_DIR/.env" ]; then
  echo "🔐 Création du fichier .env..."
  sudo tee "$PROJECT_DIR/.env" > /dev/null <<EOF
DBHOST=$DB_HOST
DBPORT=$DB_PORT
DBNAME=$DB_NAME
DBUSER=$DB_USER
DBPASS=$DB_PASS
EOF
else
  echo "ℹ️ Le fichier .env existe déjà, aucune modification."
fi

# Import de la base de données
if [ -f "$PROJECT_DIR/db.sql" ]; then
  echo "🗄️ Importation de la structure de la base de données..."
  mysql -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" -P "$DB_PORT" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME"
  mysql -u "$DB_USER" -p"$DB_PASS" -h "$DB_HOST" "$DB_NAME" < "$PROJECT_DIR/db.sql"
else
  echo "❌ Fichier db.sql introuvable dans $PROJECT_DIR"
fi

# Droits sur les fichiers
echo "🔐 Attribution des droits à Apache..."
sudo chown -R www-data:www-data "$PROJECT_DIR"
sudo chmod -R 755 "$PROJECT_DIR"

# Message final
echo "✅ Installation terminée ! Accède à ton site via : http://$(hostname -I | awk '{print $1}')"
