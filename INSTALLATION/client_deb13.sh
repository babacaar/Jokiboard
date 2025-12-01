#!/bin/bash

# Fonction pour afficher un message d'information
show_info() {
    dialog --title "$1" --msgbox "$2" 10 50
}

# Fonction pour exécuter une commande avec affichage
run_command() {
    eval "$1"
    if [ $? -eq 0 ]; then
        dialog --title "Succès" --msgbox "$2" 8 50
    else
        dialog --title "Erreur" --msgbox "Échec lors de : $2" 8 50
    fi
}

# Titre principal
dialog --title "Installation Assistée" --msgbox "Bienvenue dans le script d'installation client assistée.\n\nAppuyez sur OK pour continuer." 10 50

# Menu principal
dialog --checklist "Choisissez les composants à installer/configurer :" 20 60 10 \
1 "Installer les paquets nécessaires" on \
2 "Configurer le démarrage automatique" on \
3 "Ajouter les tâches cron" on \
4 "Créer et activer les services systemd" on \
5 "Créer les scripts .sh nécessaires" on \
6 "Configurer les certificats" on \
7 "Installer et activer le serveur FTP" off \
8 "Démarrer le service Kiosk" on \
2> /tmp/choices

CHOICES=$(cat /tmp/choices)

clear

# Étapes selon choix utilisateur
for CHOICE in $CHOICES; do
    case $CHOICE in
        1)
            show_info "Installation des paquets" "Mise à jour et installation des paquets nécessaires..."
            run_command "sudo apt update" "Mise à jour des paquets"
            run_command "sudo apt install -y  cec-utils xdotool unclutter libnss3-tools cec-utils wmctrl mpv proftpd" "Installation des outils système"
            ;;
        2)
            show_info "Configuration LightDM" "Ajout de l'utilisateur 'pi' pour autologin"
            run_command "sudo sed -i '/autologin-user=/c\autologin-user=pi' /etc/lightdm/lightdm.conf" "Modification du fichier lightdm.conf"
            ;;
        3)
            show_info "Configuration Cron" "Ajout des tâches planifiées pour l'utilisateur pi"
            crontab -u pi - <<EOF
30 7 * * 1-5 /home/pi/tv_on.sh
30 17 * * 1-5 /home/pi/tv_off.sh
0 19 * * * /home/pi/reboot.sh
0 6 * * * /home/pi/reboot.sh
30 19 * * * /home/pi/closeService.sh
30 6 * * 6-7 /home/pi/closeService.sh
EOF
            ;;
        4)
            show_info "Services Systemd" "Création des services alertFeu, alertPpms, et kiosk"

            # Exemple pour alertFeu.service
            cat <<EOF | sudo tee /lib/systemd/system/alertFeu.service >/dev/null
[Unit]
Description=Alertest Incendie
After=graphical.target
Wants=graphical.target

[Service]
ExecStart=/bin/bash /home/pi/alertFeu.sh
Restart=on-abort
User=pi
Group=pi
Environment=DISPLAY=:0.0
Environment=XAUTHORITY=/home/pi/.Xauthority

[Install]
WantedBy=graphical.target
EOF

#Service Alerte PPMS
cat <<EOF | sudo tee /lib/systemd/system/alertPpms.service > /dev/null
[Unit]
Description=Alertest Service
Wants=graphical.target
After=graphical.target

[Service]
Type=simple
ExecStart=/bin/bash /home/pi/alertPpms.sh
Restart=on-abort
User=pi
Group=pi
Environment=DISPLAY=:0.0
Environment=XAUTHORITY=/home/pi/.Xauthority

[Install]
WantedBy=graphical.target
EOF

#Service Kiosque Chromium
cat <<EOF | sudo tee /lib/systemd/system/kiosk.service > /dev/null
[Unit]
Description=Chromium Kiosk
Wants=graphical.target
After=graphical.target

[Service]
Environment=DISPLAY=:0.0
Environment=XAUTHORITY=/home/pi/.Xauthority
Type=simple
ExecStart=/bin/bash /home/pi/kiosk.sh 
Restart=on-abort
User=pi
Group=pi

[Install]
WantedBy=graphical.target
EOF
            ;;
        5)
            show_info "Création des scripts .sh" "Les scripts seront créés dans /home/pi"
            # Répéter le contenu des scripts avec `cat <<EOF` comme dans votre script initial
            cat <<EOF > /home/pi/reboot.sh
#!/bin/bash
sudo reboot
EOF

# Demander à l'utilisateur le nom d'hôte ou IP du serveur (DBHOST)
dialog --inputbox "Entrez l'adresse IP ou le nom d'hôte du serveur (DBHOST) :" 8 60 "exemple : 192.168.1.100" 2>/tmp/dbhost
DBHOST=$(cat /tmp/dbhost)

cat <<EOF | tee /home/pi/alert_feu.sh > /dev/null
#!/bin/bash

sudo systemctl start alertFeu.service
EOF

#Script Alertfeu pour lancer l'alerte incendie
cat <<EOF | tee /home/pi/alertFeu.sh > /dev/null
#!/bin/bash

sudo systemctl stop kiosk.service
sudo systemctl stop alertPpms.service

xset s noblank\n
xset s off\n
xset -dpms\n
unclutter -idle 1 -root &\n
/usr/bin/chromium-browser --kiosk --noerrdialogs http://$DBHOST/public/bar_info.php
EOF


#Script Alerte_ppms pour lancer le service
cat <<EOF | tee /home/pi/alert_ppms.sh > /dev/null
#!/bin/bash

sudo systemctl start alertPpms.service

EOF


#Script AlertPpms pour lancer l'alerte PPMS
cat <<EOF | tee /home/pi/alertPpms.sh > /dev/null
#!/bin/bash

sudo systemctl stop kiosk.service
sudo systemctl stop alertFeu.service
xset s noblank\n
xset s off\n
xset -dpms\n
unclutter -idle 1 -root &\n
/usr/bin/chromium-browser --kiosk --noerrdialogs http://$DBHOST/public/bar_ppms.php
EOF



#Script test.sh pour lancer le kiosque
cat <<'EOF' | tee /home/pi/test.sh > /dev/null
#!/bin/bash
sudo systemctl stop alertPpms.service
sudo systemctl stop alertFeu.service
date01=$(date +"%F_%T")
sudo systemctl stop kiosk.service
sudo mkdir -p /home/pi/KioskOld/$date01
sudo cp /home/pi/kiosk.sh /home/pi/KioskOld/$date01/
sudo sleep 15
sudo cp -f /home/pi/Myfiles /home/pi/kiosk.sh
sudo chmod 744 /home/pi/kiosk.sh
sudo chown root:root /home/pi/kiosk.sh
sudo dos2unix /home/pi/kiosk.sh
sudo systemctl start kiosk.service
EOF


#Script kiosk.sh pour lancer l'affichage des liens
cat <<'EOF' | tee /home/pi/kiosk.sh > /dev/null
!/bin/bash
#Compteur d'itérations
compteur=0;
#Fonction pour lancer Chromium
lancer_chromium() {
    xset s noblank
    xset s off
    xset -dpms
    unclutter -idle 1 -root &
 /usr/bin/chromium-browser --kiosk --noerrdialogs https://threatmap.checkpoint.com/  https://cybermap.kaspersky.com/fr/stats#country=177&type=OAS&period=w https://cybermap.kaspersky.com/fr
    wmctrl -k on
}

fermer_onglets_chromium() {
    xdotool search --onlyvisible --class "chromium-browser" windowfocus key ctrl+shift+w
    wmctrl -k off
}

#Fonction pour arrêter Chromium
arreter_chromium() {
    killall chromium-browser
    #Ajoutez ici dutres commandes pour nettoyer l'environnement si nécessaire
}

#Lancer Chromium au début
lancer_chromium

while true; do
    xdotool keydown ctrl+Next
    xdotool keyup ctrl+Next

    xdotool keydown ctrl+r
    xdotool keyup ctrl+r

    sleep 15

    #Incrémente le compteur d'itérations
    ((compteur++))

    #Vérifie si le nombre d'itérations spécifié est atteint
    if [ "$compteur" -eq "3" ]; then
        #Arrêtez le processus Chromium
        #arreter_chromium
        #fermer_onglets_chromium
        #Lancement de la vidéo avec VLC
        mpv --fs /home/pi/Videos/video.mp4
        #sleep 10
        #Attendez que VLC se termine avant de réinitialiser le compteur
        #Relancer Chromium après que VLC ait terminé
        lancer_chromium

        #Réinitialisez le compteur
        compteur=0
    fi
done
EOF

#Script pour lancer l'affichage de l'information ponctuelle
cat <<'EOF' | tee /home/pi/time.sh > /dev/null

#!/bin/bash
duree=$1
echo "Valeur de duree: $duree"

        if ! [[ "$duree" =~ ^[0-9]+$ ]]; then
                echo "La valeur de duree n'est pas un nombre entier positif."
                exit 1
        fi

sudo systemctl stop kiosk.service
sudo cp -f /home/pi/MyfilesInfo /home/pi/kiosk.sh
sudo chmod 744 /home/pi/kiosk.sh
sudo chown root:root /home/pi/kiosk.sh
sudo dos2unix /home/pi/kiosk.sh
sudo systemctl start kiosk.service

# Attendre que la durée soit écoulée
while [ "$duree" -gt 0 ]; do
    sleep 1
    ((duree--))
done

sudo cp -f /home/pi/Myfiles /home/pi/kiosk.sh
sudo dos2unix /home/pi/kiosk.sh
sudo systemctl restart kiosk.service
EOF

#Script reboot pour lancer le redémarrage du périphérique
cat <<EOF | tee /home/pi/tv_off.sh > /dev/null

echo 'standby 0.0.0.0' | cec-client -s -d 1

EOF

cat <<EOF | tee /home/pi/tv_on.sh > /dev/null

echo 'on 0.0.0.0' | cec-client -s -d 1

EOF

cat <<EOF | tee /home/pi/tv_statut.sh > /dev/null

echo 'pow 0.0.0.0' | cec-client -s -d 1

EOF




cat <<EOF | tee /home/pi/closeService.sh > /dev/null

# Fermer tous les onglets de Chromium ou Firefox
pkill chromium
pkill firefox

# Arrêter les services
sudo systemctl stop kiosk.service
EOF
            chmod +x /home/pi/*.sh
            ;;
        6)
            show_info "Configuration des certificats" "Création du répertoire et script d'import de certificat"
            mkdir -p /home/pi/ca_certificates
            chmod 777 /home/pi/ca_certificates
            cat <<EOF > /home/pi/cert.sh
#!/bin/bash
certutil -A -d sql:\$HOME/.pki/nssdb -t "CT,C,C" -n "CA" -i /home/pi/ca_certificates/ca_cert.cer
EOF
            chmod +x /home/pi/cert.sh
            ;;
        7)
            show_info "Installation FTP" "Installation et activation de proftpd"
            run_command "sudo apt install -y proftpd" "Installation de proftpd"
            run_command "sudo systemctl enable proftpd" "Activation du service FTP"
            ;;
        8)
            show_info "Service Kiosk" "Activation et démarrage du service"
            sudo systemctl enable kiosk.service
            sudo systemctl start kiosk.service
            ;;
    esac
done

rm -f /tmp/dbhost


dialog --title "Installation terminée" --msgbox "L'installation assistée est terminée.\nRedémarrez si nécessaire." 10 50
clear
