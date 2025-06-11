<?php
$pageTitle = "Envoi"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<body>
    <div class="page">
        <section class="page-content">
            <?php
            //ini_set('display_errors', 1);

            require_once "../config/controller_config_files.php";

            // Récupération des liens
            $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
            $stmt0 = $pdo->prepare("SELECT * FROM `configuration` ORDER BY Conf_id DESC LIMIT 1");
            $stmt0->execute();
            $res0 = $stmt0->fetch(PDO::FETCH_ASSOC); // Utilisez fetch pour obtenir une seule ligne
            $link = $res0['Conf_sites'];
            $id = $res0['Conf_id'];
            $port = '22';

            $stmt1 = $pdo->prepare("SELECT Conf_id, Conf_date, Conf_sites, LENGTH(Conf_sites) - LENGTH(REPLACE(Conf_sites, ' ', '')) +1 AS nombre_de_liens FROM `configuration` ORDER BY Conf_id DESC LIMIT 1");
            $stmt1->execute();
            $res1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $nombre_iterations = $res1['nombre_de_liens'];
            // var_dump($nombre_iterations);
            $compteur = '$compteur';
            // var_dump($compteur);
            $static = <<<BASH
#!/bin/bash
#Compteur d'itérations
compteur=0;
#Fonction pour lancer Chromium
lancer_chromium() {
    xset s noblank
    xset s off
    unclutter -idle 1 -root &
 /usr/bin/chromium-browser --kiosk --noerrdialogs $link &
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

BASH;

            $file = $dir . $names . ".sh";
            // var_dump($file);
            // Liste des adresses IP des Raspberry Pi
            $raspberryPiIPs = [];

            $selectedGroups = isset($_POST["groupIDs"]) ? $_POST["groupIDs"] : [];
            // var_dump($selectedGroups);
            try {
                // Établir une connexion à la base de données
                $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);

                // Boucle sur chaque groupe sélectionné
                foreach ($selectedGroups as $groupId) {
                    // Récupérez les adresses IP, username et password des Raspberry Pi pour ce groupe depuis la base de données
                    $query = "SELECT p.ip, p.username, p.password, p.name, p.video_acceptance 
                                FROM pis p
                                JOIN pis_groups pg ON p.id = pi_id
                                WHERE pg.group_id = :group_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(":group_id", $groupId, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        // Utilisez fetchAll pour obtenir toutes les lignes de résultats
                        $raspberryPiIPs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Vérifier si le groupe est vide
                        if (empty($raspberryPiIPs)) {
                            throw new Exception("Aucun hôte trouvé pour le groupe sélectionné.");
                        }

                        // Boucle sur chaque Hôte/Raspberry Pi du groupe
                        foreach ($raspberryPiIPs as $raspberryPiInfo) {
                            $ip = $raspberryPiInfo['ip'];
                            $username = $raspberryPiInfo['username'];
                            $password = $raspberryPiInfo['password'];
                            $video_acceptance = $raspberryPiInfo['video_acceptance'];
			                      $name = $raspberryPiInfo['name'];

                            $fichier = @fopen($file, 'w');
                            if ($fichier === false) {
                                throw new Exception("Erreur lors de l'ouverture du fichier.");
                            }
                            fwrite($fichier, $static);

                            if ($video_acceptance == 1) {
                                fwrite($fichier, <<<BASH

        #Incrémente le compteur d'itérations
    ((compteur++))

    #Vérifie si le nombre d'itérations spécifié est atteint
    if [ "$compteur" -eq "$nombre_iterations" ]; then
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
done\n

BASH
);
	} else {
    		fwrite($fichier, <<<BASH
        	#Aucune vidéo à lancer car video_acceptance n'est pas égal à 1
        	#Relancer Chromium et réinitialiser le compteur

done\n
BASH
);
			    }

                           // fwrite($fichier, <<<BASH

        //#Relancer Chromium après que VLC ait terminé
        //lancer_chromium

        //#Réinitialisez le compteur
        //compteur=0
    //fi
//done\n
//BASH
//);

                            fclose($fichier);


                            // Tentative de connexion au serveur FTP
                            //echo "Tentative de connexion au serveur FTP $ip\n";
                            $identifiant_Srv = ftp_connect($ip) or die("could not connect to $ip");

                            if (@ftp_login($identifiant_Srv, $username, $password)) {
                                $msg = "Connecté en tant que $name";
                                include "../modules/success.php";

                            } else {
                                throw new Exception("Connexion impossible en tant que $name");
                            }
                            // var_dump($username);
                            // Transfert du fichier
                            $remote_file = $names;
                            ftp_put($identifiant_Srv, $remote_file, $file, FTP_ASCII);
                            ftp_close($identifiant_Srv);


                            // Exécution du script
                            $connection = ssh2_connect($ip, $port);
                            ssh2_auth_password($connection, $username, $password);
                            $stream = ssh2_exec($connection, "/home/pi/test.sh");
                            stream_set_blocking($stream, true);
                            $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                            $stream_out = stream_get_contents($stream_out);

                            if ($stream_out === false) {
                                throw new Exception("Erreur lors de l'exécution du script sur $ip");
                            }

                            echo trim($stream_out);
                            // var_dump($username);
                            ssh2_disconnect($connection);
                            unset($connection);

                            $msg = "Script exécuté avec succès sur $ip";
                            include "../modules/success.php";
                        }
                    } else {
                        $msg = "Erreur lors de l'exécution de la requête SQL : " . print_r($stmt->errorInfo(), true);
                        include "../modules/error.php";
                    }
                }

            } catch (PDOException $e) {
                $msg = "Erreur de connexion à la base de données : " . $e->getMessage();
                include "../modules/error.php";
            } catch (Exception $e) {
                $msg = $e->getMessage();
                include "../modules/error.php";
            }

            ?>

        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
