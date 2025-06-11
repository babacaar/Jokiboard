<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "controllers/controller_config_files.php";

// Vérifier si le formulaire a été soumis et si un fichier a été téléchargé
if(isset($_POST["submit"]) && isset($_FILES["video"])) {
    $targetDir = "$Url/Videos/";
    $targetFile = $targetDir . "video.mp4"; // Renommer le fichier téléchargé en "video.mp4"

    // Vérifier si le fichier vidéo est une vidéo réelle ou une fausse vidéo
    $check = getimagesize($_FILES["video"]["tmp_name"]);
    if($check !== false) {
        echo "Le fichier est une vidéo.";
    } else {
        echo "Le fichier n'est pas une vidéo.";
    }

    // Afficher les éventuelles erreurs lors du téléchargement
    if ($_FILES["video"]["error"] > 0) {
        echo "Erreur de téléchargement : " . $_FILES["video"]["error"];
    } else {
        // Si tout est OK, essayez de télécharger le fichier en écrasant les fichiers existants
        if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFile)) {
            echo "Le fichier a été téléchargé.";

            // Parcourir chaque groupe sélectionné
            foreach ($_POST["groupIDs"] as $groupId) {
                try {
                    $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
                    // Récupérer les adresses IP, nom d'utilisateur et mot de passe des Raspberry Pi pour ce groupe depuis la base de données
                    $query = "SELECT pis.ip, pis.username, pis.password FROM pis INNER JOIN pis_groups ON pis.id = pis_groups.pi_id WHERE pis_groups.group_id = :group_id";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(":group_id", $groupId, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        // Boucle sur chaque Raspberry Pi du groupe
                        while ($raspberryPiInfo = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $ip = $raspberryPiInfo['ip'];
                            $username = $raspberryPiInfo['username'];
                            $password = $raspberryPiInfo['password'];

                            // Connexion au serveur FTP du Raspberry Pi
                            $conn_id = ftp_connect($ip);
                            $login_result = ftp_login($conn_id, $username, $password);

                            // Vérifier la connexion
                            if (!$conn_id || !$login_result) {
                                echo "Impossible de se connecter au serveur FTP du Raspberry Pi $ip.";
                                continue; // Passer au Raspberry Pi suivant
                            }

                            // Transférer le fichier via FTP en écrasant les fichiers existants
                            if (ftp_put($conn_id, "/home/pi/Videos/video.mp4", $targetFile, FTP_BINARY)) {
                                echo "Le fichier a été transféré avec succès sur le Raspberry Pi $ip.";
                            } else {
                                echo "Erreur lors du transfert du fichier sur le Raspberry Pi $ip.";
                            }

                            // Fermer la connexion FTP
                            ftp_close($conn_id);
                        }
                    }
                } catch (PDOException $e) {
                    echo "Erreur PDO : " . $e->getMessage();
                }
            }
        } else {
            echo "Une erreur est survenue lors du téléchargement de votre fichier.";
        }
    }
}
?>
