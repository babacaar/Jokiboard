
<?php
$pageTitle = "Alerte"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>



<body>
    <div class="page">
        <section class="page-content">
            <h1>L'alerte est lancée, mettez-vous en sécurité à présent.</h1>
            <hr>
            <a tabindex="0" href='<?php  require_once "../config/controller_config_files.php"; 
                                          echo $Url . "/groupe.php" ?>' class="back-btn"><i
                    class="fa-solid fa-arrow-right-from-bracket fa-rotate-180"></i>Retour</a>

		<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once "../config/controller_config_files.php";
$port = '22';
$raspberryPiIPs = [];

$selectedGroups = isset($_POST["groupIDs"]) ? $_POST["groupIDs"] : [];
//var_dump($selectedGroups);

try {
    // Établir une connexion à la base de données
    $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
    // Boucle sur chaque groupe sélectionné
    foreach ($selectedGroups as $groupId) {
        // Récupérez les adresses IP, username et password des Raspberry Pi pour ce groupe depuis la base de données
        $query = "SELECT pis.ip, pis.username, pis.password FROM pis INNER JOIN pis_groups ON pis.id = pis_groups.pi_id WHERE pis_groups.group_id = :group_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":group_id", $groupId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Utilisez fetchAll pour obtenir toutes les lignes de résultats
            $raspberryPiIPs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Boucle sur chaque Raspberry Pi du groupe
            foreach ($raspberryPiIPs as $raspberryPiInfo) {
                !$ip = $raspberryPiInfo['ip'];
                $username = $raspberryPiInfo['username'];
                $password = $raspberryPiInfo['password'];

                // Exécution du script
                $connection = ssh2_connect($ip, $port);
                ssh2_auth_password($connection, $username, $password);
                $stream = ssh2_exec($connection, "/home/pi/alert_feu.sh");
                stream_set_blocking($stream, true);
                $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                $stream_out = stream_get_contents($stream_out);
                echo trim($stream_out);
                ssh2_disconnect($connection);
                unset($connection);
            }
        } else {
            echo "<p class='debug-msg'>Erreur lors de l'exécution de la requête SQL : " . print_r($stmt->errorInfo(), true) . "</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p class='debug-msg'>Erreur de connexion à la base de données : " . $e->getMessage() . "</p>";
}

	?>

	</section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
