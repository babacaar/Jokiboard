<?php
header('Content-Type: application/json');
set_time_limit(0);

$msg = "";
$response = ['success' => false, 'message' => 'Erreur inconnue'];

try {
    require_once "../config/controller_config_files.php";
    $siteUrl = "http://$dbhost";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);

        $titre = $_POST['titre'] ?? '';
        $info = $_POST['infos'] ?? '';
        $duration = $_POST['duration'] ?? '00:00:00';
        list($hours, $minutes, $seconds) = array_map('intval', explode(':', $duration));
        $durationInSeconds = $hours * 3600 + $minutes * 60 + $seconds;

        $insertStmt = $pdo->prepare("INSERT INTO Informations (titre, infos, duration_seconds) VALUES (:titre, :infos, :durationSeconds)");
        $insertStmt->bindParam(':titre', $titre);
        $insertStmt->bindParam(':infos', $info);
        $insertStmt->bindParam(':durationSeconds', $durationInSeconds);

        if (!$insertStmt->execute()) {
            throw new Exception("Erreur insertion : " . $insertStmt->errorInfo()[2]);
        }

        $stmt1 = $pdo->prepare("SELECT infos, duration_seconds FROM Informations ORDER BY id DESC LIMIT 1");
        $stmt1->execute();
        $res1 = $stmt1->fetch(PDO::FETCH_ASSOC);
        $news = $res1['infos'];
        $durationInSeconds = $res1['duration_seconds'];

        $stmt0 = $pdo->prepare("SELECT * FROM configuration ORDER BY Conf_id DESC LIMIT 1");
        $stmt0->execute();
        $res0 = $stmt0->fetch(PDO::FETCH_ASSOC);
        $link = $res0['Conf_sites'];
        $port = '22';
        $news = $siteUrl . "/public/display_info.php";

        $stmt2 = $pdo->prepare("SELECT LENGTH(Conf_sites) - LENGTH(REPLACE(Conf_sites, ' ', '')) + 2 AS nombre_de_liens FROM configuration ORDER BY Conf_id DESC LIMIT 1");
        $stmt2->execute();
        $res2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        $nombre_iterations = $res2['nombre_de_liens'];

        $selectedGroups = $_POST["group_id"] ?? [];

        foreach ($selectedGroups as $groupId) {
            $query = "SELECT p.name, p.ip, p.username, p.password, p.video_acceptance
                      FROM pis p
                      JOIN pis_groups pg ON p.id = pg.pi_id
                      WHERE pg.group_id = :group_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":group_id", $groupId, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Erreur SQL : " . print_r($stmt->errorInfo(), true));
            }

            $raspberryPiIPs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($raspberryPiIPs)) {
                throw new Exception("Aucun hôte trouvé pour le groupe sélectionné.");
            }

            foreach ($raspberryPiIPs as $raspberryPiInfo) {
                $ip = $raspberryPiInfo['ip'];
                $username = $raspberryPiInfo['username'];
                $password = $raspberryPiInfo['password'];
                $video_acceptance = $raspberryPiInfo['video_acceptance'];
                $name = $raspberryPiInfo['name'];

                $file = $dir . $nom . ".sh";

                $script = <<<BASH
#!/bin/bash
compteur=0;
duree=$durationInSeconds;

lancer_chromium() {
    xset s noblank
    xset s off
    xset -dpms
    unclutter -idle 1 -root &
    /usr/bin/chromium-browser --kiosk --noerrdialogs $news $link &
}

fermer_onglets_chromium() {
    xdotool search --onlyvisible --class "chromium-browser" windowfocus key ctrl+shift+w
    wmctrl -k off
}

arreter_chromium() {
    killall chromium-browser
}

lancer_chromium

while true; do
    xdotool keydown ctrl+Next
    xdotool keyup ctrl+Next
    xdotool keydown ctrl+r
    xdotool keyup ctrl+r
    sleep 15
BASH;

                if ($video_acceptance == 1) {
                    $script .= <<<BASH

    ((compteur++))
    if [ "\$compteur" -eq "$nombre_iterations" ]; then
        fermer_onglets_chromium
        mpv --fs /home/pi/Videos/video.mp4
        sleep 10
        lancer_chromium
        compteur=0
    fi

BASH;
                } else {
                    $script .= <<<BASH
    # Pas de vidéo à lancer pour ce Raspberry Pi (video_acceptance != 1)

BASH;
                }

                $script .= "done\n";

                if (file_put_contents($file, $script) === false) {
                    throw new Exception("Erreur d'écriture dans le fichier $file.");
                }

                // FTP upload
                $ftp = @ftp_connect($ip);
                if (!$ftp || !@ftp_login($ftp, $username, $password)) {
                    throw new Exception("Connexion FTP échouée à $ip");
                }
                ftp_put($ftp, $nom, $file, FTP_ASCII);
                ftp_close($ftp);

                // SSH exec
                $ssh = @ssh2_connect($ip, $port);
                if (!$ssh || !ssh2_auth_password($ssh, $username, $password)) {
                    throw new Exception("Connexion SSH échouée à $ip");
                }

                $ssh_command = "/home/pi/time.sh $durationInSeconds";
                $stream = ssh2_exec($ssh, $ssh_command);
                stream_set_blocking($stream, true);
                $output = stream_get_contents($stream);
                fclose($stream);

                $msg .= "Script exécuté sur $ip avec succès.\n";
            }
        }

        $response = ['success' => true, 'message' => $msg];
    } else {
        throw new Exception("Méthode non autorisée.");
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
