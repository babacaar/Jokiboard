🖥️ Affichage Dynamique pour Raspberry Pi  
Ce projet PHP permet de gérer l’affichage de contenus (liens web) sur des Raspberry Pi à distance, avec la possibilité d’intercaler des vidéos.  
Le tout est automatisé grâce à un script généré dynamiquement, envoyé par SSH/FTP.

_________________________________________________________________________

📁 Structure du projet

```
mon-projet/
├── controllers/           # Fichiers de logique (controller_config.php, controller_config_files.php)
├── modules/               # Contient header.php, footer.php, success.php, error.php
├── images/                # Contient logo_transparent.jpg (Le logo à personnaliser) 
├── Videos/                # Dossier prévu pour les vidéos (lecture via mpv)
├── db.sql                 # Dump SQL (structure uniquement)
├── .env                   # Variables d’environnement (non versionné)
├── menu.php               # Page d’accueil ou point d’entrée
├── envoi.php              # Configuration et gestion des exécutions via SSH 
└── .gitignore             # Fichiers/dossiers à ne pas suivre dans Git
```
_________________________________________________________________________

⚙️ Configuration requise

- PHP 8.2  
- Serveur web (Apache, Nginx)  
- MySQL/MariaDB  
- Modules PHP : PDO, ssh2, ftp, mbstring  
- Un environnement Linux (pour exécution des scripts .sh sur Raspberry Pi)  

_________________________________________________________________________

🛠️ Installation manuelle

1. Clone du dépôt

```bash
git clone https://github.com/babacaar/Projet_Raspberry.git
cd Projet_Raspberry/
```

2. Configurer l'environnement  
Crée un fichier `.env` à la racine :

```
DBHOST=votre ip
DBPORT=3306
DBNAME=nom_de_ta_bdd (affichage)
DBUSER=ton_utilisateur
DBPASS=ton_mot_de_passe
```

3. Importer la base de données

```bash
mysql -u utilisateur -p base_de_donnees < db.sql
```

4. Droits  
Assure-toi que le serveur web a le droit d’écriture.

___________________________________________________________________________________

🛠️ Installation classique avec script

Exécuter le script `install.sh` présent dans le dossier `INSTALLATION/`

_________________________________________________________________________

🛠️ Installation assistée

Exécuter le script d'installation assistée `choix_d_installation.sh` (avant de l'exécuter assurez-vous d'installer `dialog` avec :  
```bash
sudo apt install dialog
```
une boite de dialogue vous proposera 3 options Mode Client, Mode Serveur ou Serveur + Client ; Y'a plus qu'à suivre la démarche

_________________________________________________________________________

🚀 Utilisation

1. Accède à l’interface web.  
2. Ajoute les liens à afficher.  
3. Crée un groupe et associe des Raspberry Pi (IP, user, password).  
4. Lance l’envoi des scripts.  
5. Les Raspberry Pi exécutent automatiquement Chromium ou mpv.

_________________________________________________________________________

🔐 Sécurité

Les mots de passe Raspberry sont stockés pour les connexions FTP/SSH. Pour un usage en production, prévois un chiffrement ou une solution plus sécurisée.

Le fichier `.env` est ignoré par Git pour éviter les fuites de données sensibles.

_____________________________________________________________________________________________________________________________  
✍️ Auteur 
Développé avec ❤️ par babacaar  
📧 Contact : techinfo@lpjw.fr  
🔗 GitHub : github.com/babacaar
