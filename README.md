# 🎓 Jokiboard – Affichage Dynamique pour Raspberry Pi  

## Table des matières
- [Présentation](#présentation)
- [Fonctionnalités principales](#fonctionnalités-principales)
- [Technologies](#technologies)
- [Structure du projet](#structure-du-projet)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Sécurité](#sécurité)
- [Auteur](#auteur)
_________________________________________________________________________

## 📌 Présentation

**Jokiboard** est une plateforme web d'affichage dynamique développée pour moderniser la communication interne des **établissements scolaires(LPJW)**.  
L'application permet de diffuser, organiser et automatiser les informations importantes sur des écrans connectés (via Raspberry Pi) au sein de l'établissement.

> 🎯 Objectif : renforcer la communication, la modernisation et la gestion des absences, via une interface web centralisée.

_________________________________________________________________________
## 🧩 Fonctionnalités Principale

```
- 📺 **Affichage dynamique** : diffusion d’infos ponctuelles, menus, alertes...
- 👨‍🏫 **Gestion des absences** (saisie & consultation)
- 🖥️ **Administration des écrans** (groupe, hôtes Raspberry Pi)
- 🎛️ **Interface personnalisée** par utilisateur
- 📅 **Planification des contenus**
- 🧠 **Envoi de scripts & contrôle à distance via SSH/FTP**
- 📷 **Conversion automatique de pages en images pour affichage**
- 🔐 **Connexion sécurisée**
```
_________________________________________________________________________

## 🖥️ Technologies

     🔧 Architecture Technique (LAMP)
- **Linux** – Système principal
- **Apache** – Serveur web
- **MySQL** – Base de données
- **PHP** – Backend principal

     🧱 Frontend
- **HTML / CSS** – Interface responsive
- **JavaScript** – Interactions (formulaires, AJAX)

_________________________________________________________________________

## 📁 Structure du projet

```bash
.
├── public/                 # Pages accessibles publiquement
├── controllers/           # Traitements des formulaires
├── config/                # Fichiers de configuration
├── modules/               # Header, footer, menus...
├── assets/                # Images, JS, CSS
├── scripts/               # Fichiers .sh pour Raspberry Pi
├── database/              # Dump SQL
└── INSTALLATION/          # Scripts d'installation du projet
```

_________________________________________________________________________

## 🚀 Déploiement

    ### ⚙️ Configuration requise

    - PHP 8.2  
    - Serveur web (Apache, Nginx)  
    - MySQL/MariaDB  
    - Modules PHP : PDO, ssh2, ftp, mbstring  
    - Composer (optionnel pour PHPMailer)
    - Un environnement Linux (pour exécution des scripts .sh sur Raspberry Pi)  



    ### 🛠️ Installation manuelle

        1. Clone du dépôt

            ```bash
            git clone https://github.com/babacaar/Jokiboard.git
            cd Jokiboard/
            ```

        2. Configurer l'environnement  
        Crée un fichier `.env` dans le dossier config

            ```
            DBHOST=votre ip
            DBPORT=3306
            DBNAME=nom_de_ta_bdd (affichage)
            DBUSER=ton_utilisateur
            DBPASS=ton_mot_de_passe
            ```

        3. Importer la base de données
        Importer la BDD présente dans le dossier **database**

            ```bash
            mysql -u utilisateur -p base_de_donnees < db.sql
            ```

        4. Droits  
        Assure-toi que le serveur web a le droit d’écriture.

            ```bash
            chown -R www-data:www-data chemin/du/projet
            ```

        Ne pas oublier de configurer le Virtual Host Apache



    ### 🛠️ Installation classique avec script

    Exécuter le script `install.sh` présent dans le dossier `INSTALLATION/` Ou lancer simplement la commande suivante :
    `Noter qu'avec ce script les accès databases, variables d'environnement, dossier de projet sont prédéfinis !`

        ```bash
            sudo curl -sO https://raw.githubusercontent.com/babacaar/Jokiboard/refs/heads/main/INSTALLATION/install.sh && bash install.sh
        ```


    ### 🛠️ Installation assistée (GUI)

    Exécuter le script d'installation assistée `choix_d_installation.sh` (avant de l'exécuter assurez-vous d'installer `dialog` avec :  

        ```bash
        sudo apt install dialog
        ```

        ```bash
        sudo curl -sO https://raw.githubusercontent.com/babacaar/Jokiboard/refs/heads/main/INSTALLATION/choix_d_installation.sh && bash choix_d_installation.sh
        ```

    `Une boite de dialogue vous proposera 3 options Mode Client, Mode Serveur ou Serveur + Client ; Y'a plus qu'à suivre la démarche`

_________________________________________________________________________

## 🚀 Utilisation

1. Accède à l’interface web.  
2. Ajoute les liens à afficher.  
3. Crée un groupe et associe des Raspberry Pi (IP, user, password).  
4. Lance l’envoi des scripts. 
    (Bouton `Rafraichir` de la page `groupe.php` pour diffuser les liens ajoutés) 
5. Les Raspberry Pi exécutent automatiquement Chromium ou mpv.

_________________________________________________________________________

## 🔐 Sécurité

- 🔒 Mots de passe hashés
- ✅ Sessions PHP vérifiées sur chaque page sensible
- 🔐 SSH/FTP sécurisé pour le contrôle distant des Raspberry Pi

Le fichier `.env` est ignoré par Git pour éviter les fuites de données sensibles.

_____________________________________________________________________________________________________________________________  
## ✍️ Auteur 
Développé avec ❤️ par babacaar  
📧 Contact : techinfo@lpjw.fr  
🔗 GitHub : github.com/babacaar
