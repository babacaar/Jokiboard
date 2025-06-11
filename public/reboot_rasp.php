<!------------HEADER------------>
<?php
$pageTitle = "Gestion des Groupes"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>
<body>
    <div class="group page">
        <section class="page-content">
<?php
//ini_set('display_errors', 1);

require_once "../config/controller_config_files.php";

// Établir une connexion à la base de données
$pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);

// Liste des adresses IP des Raspberry Pi
$raspberryPiIPs = [];
$port = '22';
$selectedGroups = isset($_POST["groupIDs"]) ? $_POST["groupIDs"] : [];
//var_dump($selectedGroups);

try {
    // Boucle sur chaque groupe sélectionné
    foreach ($selectedGroups as $groupId) {
        // Récupérez les adresses IP, username et password des Raspberry Pi pour ce groupe depuis la base de données
        $query = "SELECT p.ip, p.username, p.password 
                    FROM pis p
                    JOIN pis_groups pg ON p.id = pi_id
                    WHERE pg.group_id = :group_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":group_id", $groupId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $raspberryPiIPs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Boucle sur chaque Raspberry Pi du groupe
            foreach ($raspberryPiIPs as $raspberryPiInfo) {
                $ip = $raspberryPiInfo['ip'];
                $username = $raspberryPiInfo['username'];
                $password = $raspberryPiInfo['password'];

                // Établissez une connexion SSH
                $connection = ssh2_connect($ip, $port);
                if (ssh2_auth_password($connection, $username, $password)) {
                    $stream = ssh2_exec($connection, '/home/pi/reboot.sh');
                    stream_set_blocking($stream, true);
                    $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                    //$stream_out = stream_get_contents($stream_out);
		    $msg = "Script de redémarrage exécuté sur $ip :\n";
                    include "modules/success.php";
                   // echo "Script de redémarrage exécuté sur $ip :\n";
                    //echo trim($stream_out) . "\n\n";
                    ssh2_disconnect($connection);
                } else {
                    echo "Impossible de se connecter à $ip avec le nom d'utilisateur $username.\n";
                }
            }
        } else {
            echo "Erreur lors de l'exécution de la requête SQL : " . print_r($stmt->errorInfo(), true);
        }
    }
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}
?>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
