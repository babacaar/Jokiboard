#!/bin/bash

# Usage: sudo bash install_client_no_dialog.sh <DB_HOST>

if [ "$EUID" -ne 0 ]; then
  echo "Ce script doit être exécuté en tant que root."
  exit 1
fi

if [ -z "$1" ]; then
  echo "Usage: $0 <DB_HOST>"
  exit 1
fi

DBHOST=$1

# Installation des paquets
apt update
apt install -y cec-utils xdotool unclutter libnss3-tools wmctrl mpv proftpd

# Configuration LightDM pour autologin
sed -i '/autologin-user=/c\autologin-user=pi' /etc/lightdm/lightdm.conf

# Ajout des crons pour pi
crontab -u pi - <<EOF
30 7 * * 1-5 /home/pi/tv_on.sh
30 17 * * 1-5 /home/pi/tv_off.sh
0 19 * * * /home/pi/reboot.sh
0 6 * * * /home/pi/reboot.sh
30 19 * * * /home/pi/closeService.sh
30 6 * * 6-7 /home/pi/closeService.sh
EOF

# Services systemd
cat <<EOF > /lib/systemd/system/alertFeu.service
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

cat <<EOF > /lib/systemd/system/alertPpms.service
[Unit]
Description=Alertest Service
After=graphical.target
Wants=graphical.target

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

cat <<EOF > /lib/systemd/system/kiosk.service
[Unit]
Description=Chromium Kiosk
After=graphical.target
Wants=graphical.target

[Service]
Type=simple
ExecStart=/bin/bash /home/pi/kiosk.sh
Restart=on-abort
User=pi
Group=pi
Environment=DISPLAY=:0.0
Environment=XAUTHORITY=/home/pi/.Xauthority

[Install]
WantedBy=graphical.target
EOF

# Création des scripts
mkdir -p /home/pi/

cat <<EOF > /home/pi/reboot.sh
#!/bin/bash
sudo reboot
EOF

cat <<EOF > /home/pi/alert_feu.sh
#!/bin/bash
sudo systemctl start alertFeu.service
EOF

cat <<EOF > /home/pi/alertFeu.sh
#!/bin/bash
sudo systemctl stop kiosk.service
sudo systemctl stop alertPpms.service
xset s noblank
xset s off
xset -dpms
unclutter -idle 1 -root &
/usr/bin/chromium-browser --kiosk --noerrdialogs http://$DBHOST/public/bar_info.php
EOF

cat <<EOF > /home/pi/alert_ppms.sh
#!/bin/bash
sudo systemctl start alertPpms.service
EOF

cat <<EOF > /home/pi/alertPpms.sh
#!/bin/bash
sudo systemctl stop kiosk.service
sudo systemctl stop alertFeu.service
xset s noblank
xset s off
xset -dpms
unclutter -idle 1 -root &
/usr/bin/chromium-browser --kiosk --noerrdialogs http://$DBHOST/public/bar_ppms.php
EOF

cat <<'EOF' > /home/pi/test.sh
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

cat <<'EOF' > /home/pi/kiosk.sh
#!/bin/bash
compteur=0
lancer_chromium() {
    xset s noblank
    xset s off
    xset -dpms
    unclutter -idle 1 -root &
    /usr/bin/chromium-browser --kiosk --noerrdialogs https://threatmap.checkpoint.com/ https://cybermap.kaspersky.com/fr/stats#country=177&type=OAS&period=w https://cybermap.kaspersky.com/fr
    wmctrl -k on
}
while true; do
    lancer_chromium
    xdotool keydown ctrl+Next; xdotool keyup ctrl+Next
    xdotool keydown ctrl+r; xdotool keyup ctrl+r
    sleep 15
    ((compteur++))
    if [ "$compteur" -eq "3" ]; then
        mpv --fs /home/pi/Videos/video.mp4
        compteur=0
    fi
done
EOF

cat <<'EOF' > /home/pi/time.sh
#!/bin/bash
duree=$1
if ! [[ "$duree" =~ ^[0-9]+$ ]]; then exit 1; fi
sudo systemctl stop kiosk.service
sudo cp -f /home/pi/MyfilesInfo /home/pi/kiosk.sh
sudo chmod 744 /home/pi/kiosk.sh
sudo chown root:root /home/pi/kiosk.sh
dos2unix /home/pi/kiosk.sh
sudo systemctl start kiosk.service
while [ "$duree" -gt 0 ]; do sleep 1; ((duree--)); done
sudo cp -f /home/pi/Myfiles /home/pi/kiosk.sh
dos2unix /home/pi/kiosk.sh
sudo systemctl restart kiosk.service
EOF

cat <<EOF > /home/pi/tv_off.sh
echo 'standby 0.0.0.0' | cec-client -s -d 1
EOF

cat <<EOF > /home/pi/tv_on.sh
echo 'on 0.0.0.0' | cec-client -s -d 1
EOF

cat <<EOF > /home/pi/tv_statut.sh
echo 'pow 0.0.0.0' | cec-client -s -d 1
EOF

cat <<EOF > /home/pi/closeService.sh
pkill chromium
pkill firefox
sudo systemctl stop kiosk.service
EOF

chmod +x /home/pi/*.sh

# Configuration certificats (répertoire vide)
mkdir -p /home/pi/ca_certificates
chmod 777 /home/pi/ca_certificates
cat <<EOF > /home/pi/cert.sh
#!/bin/bash
certutil -A -d sql:\$HOME/.pki/nssdb -t "CT,C,C" -n "CA" -i /home/pi/ca_certificates/ca_cert.cer
EOF
chmod +x /home/pi/cert.sh

# Activer les services
systemctl enable alertFeu.service
systemctl enable alertPpms.service
systemctl enable kiosk.service

systemctl start kiosk.service
