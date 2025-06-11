#!/bin/bash

# === Variables ===
DEFAULT_PROJECT_DIR="/var/www/monsite.fr"
REPO_URL="https://github.com/babacaar/Projet_Raspberry.git"
CLIENT_SCRIPT_URL="https://raw.githubusercontent.com/babacaar/Projet_Raspberry/master/INSTALLATION/client.sh"
APACHE_USER="www-data"

# === Vérifie droits root ===
if [ "$EUID" -ne 0 ]; then
    echo "❌ Veuillez exécuter ce script avec sudo ou en tant que root."
    exit 1
fi

# === Vérifie que dialog est installé ===
if ! command -v dialog &> /dev/null; then
    echo "dialog n'est pas installé. Installez-le avec : sudo apt install dialog"
    exit 1
fi

# === Fenêtre de bienvenue ===
dialog --title "Installation Affichage Dynamique" --msgbox "Bienvenue dans l'assistant d'installation du projet pour Raspberry Pi.\n\nAppuie sur OK pour commencer." 10 60

# === Choix du type d'installation ===
dialog --title "Choix de l'installation" --menu "Que souhaitez-vous installer ?" 15 60 4 \
1 "Installer uniquement le CLIENT" \
2 "Installer uniquement le SERVEUR" \
3 "Installer le CLIENT et le SERVEUR (même hôte)" \
4 "Quitter" 2> /tmp/install_choice

CHOICE=$(< /tmp/install_choice)
clear

# === Lancement selon le choix ===
case "$CHOICE" in
    1)
        # Téléchargement direct de client.sh sans cloner le dépôt
        TMP_CLIENT="/tmp/client.sh"
        dialog --infobox "📥 Téléchargement du script client..." 5 50
        if curl -fsSL "$CLIENT_SCRIPT_URL" -o "$TMP_CLIENT"; then
            chmod +x "$TMP_CLIENT"
            "$TMP_CLIENT"
        else
            dialog --msgbox "❌ Échec du téléchargement du script client depuis GitHub." 7 60
            rm -f /tmp/install_choice
            exit 1
        fi
        ;;

    2|3)
        # === Choix du dossier d'installation ===
        dialog --inputbox "Choisis le dossier d'installation du projet :" 8 60 "$DEFAULT_PROJECT_DIR" 2>project_dir.txt
        PROJECT_DIR=$(<project_dir.txt)
        INSTALL_DIR="$PROJECT_DIR/INSTALLATION"
        mkdir -p "$PROJECT_DIR"

        # === Nettoyage si dossier non vide ===
        if [ -d "$PROJECT_DIR" ] && [ "$(ls -A "$PROJECT_DIR")" ]; then
            dialog --yesno "Le dossier $PROJECT_DIR existe et n'est pas vide.\nVeux-tu supprimer son contenu et re-cloner le dépôt ?" 10 60
            if [ $? -eq 0 ]; then
                find "$PROJECT_DIR" -mindepth 1 -exec rm -rf {} +
            else
                dialog --msgbox "Installation annulée car dossier non vide." 6 50
                rm -f project_dir.txt /tmp/install_choice
                exit 1
            fi
        fi

        # === Clonage du dépôt Git ===
        dialog --infobox "📥 Clonage du dépôt Git dans $PROJECT_DIR ..." 5 60
        apt update -y && apt install -y git
        if ! git clone --branch master "$REPO_URL" "$PROJECT_DIR"; then
            dialog --msgbox "❌ Erreur lors du clonage du dépôt Git." 6 50
            rm -f project_dir.txt /tmp/install_choice
            exit 1
        fi

        # === Barre de progression fictive ===
        {
            echo 25; sleep 0.5
            echo 50; sleep 0.5
            echo 75; sleep 0.5
            echo 100; sleep 0.3
        } | dialog --title "Progression" --gauge "📦 Préparation de l'installation..." 10 60 0

        # === Exécution des scripts serveur / client ===
        run_script() {
            SCRIPT_PATH="$1"
            if [ -f "$SCRIPT_PATH" ]; then
                chmod +x "$SCRIPT_PATH"
                "$SCRIPT_PATH"
            else
                dialog --title "Erreur" --msgbox "❌ Le script $SCRIPT_PATH est introuvable !" 8 50
                exit 1
            fi
        }

        if [ "$CHOICE" = "2" ]; then
            run_script "$INSTALL_DIR/install_srv_assist.sh"
        elif [ "$CHOICE" = "3" ]; then
            run_script "$INSTALL_DIR/install_srv_assist.sh"
            run_script "$INSTALL_DIR/client.sh"
        fi
        ;;

    4|*)
        dialog --title "Annulé" --msgbox "Installation annulée par l'utilisateur." 8 40
        rm -f project_dir.txt /tmp/install_choice
        exit 0
        ;;
esac

# === Fin ===
dialog --title "Installation complète" --msgbox "✅ Tous les composants sélectionnés ont été installés avec succès." 8 50

# === Nettoyage ===
rm -f project_dir.txt /tmp/install_choice
clear
