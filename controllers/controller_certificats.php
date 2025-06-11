<!------------HEADER------------>
<?php
$pageTitle = "Téléchargement"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="page">
        <section class="page-content">
<?php
require_once "../config/controller_config_files.php";
// Vérifier si le formulaire a été soumis et si un fichier a été téléchargé
if(isset($_POST["submit"]) && isset($_FILES["cerFile"])) {
    $targetDir = "$Url/ca_certificates/";
    $fixedFileName = "ca_cert.cer"; // Nom fixe pour le fichier téléchargé
    $targetFile = $targetDir . $fixedFileName;
    $port='22';

    // Vérifier si le fichier est un fichier .cer
    $fileInfo = pathinfo($_FILES["cerFile"]["name"]);
    if(strtolower($fileInfo["extension"]) !== "cer") {
        echo "Le fichier n'est pas un fichier .cer.";
        exit;
    }

    // Afficher les éventuelles erreurs lors du téléchargement
    if ($_FILES["cerFile"]["error"] > 0) {
        $msg = "Erreur de téléchargement : " . $_FILES["cerFile"]["error"];
        include "../modules/error.php";
    } else {
        // Si tout est OK, essayez de télécharger le fichier en écrasant les fichiers existants
        if (move_uploaded_file($_FILES["cerFile"]["tmp_name"], $targetFile)) {
            echo "Le fichier .cer a été téléchargé avec succès.";

            // Récupérer les données du formulaire
            $raspberryIP = $_POST['raspberryIP'];
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Connexion au serveur FTP du Raspberry Pi
            $conn_id = ftp_connect($raspberryIP);
            $login_result = ftp_login($conn_id, $username, $password);

            // Vérifier la connexion
            if (!$conn_id || !$login_result) {
                echo "Impossible de se connecter au serveur FTP du Raspberry Pi $raspberryIP.";
            }

            // Transférer le fichier via FTP en écrasant les fichiers existants
            if (ftp_put($conn_id, "/home/pi/ca_certificates/" . $fixedFileName, $targetFile, FTP_BINARY)) {
                echo "Le fichier a été transféré avec succès sur le Raspberry Pi $raspberryIP.";

                // Exécution du script via SSH
                $connection = ssh2_connect($raspberryIP, $port);
                if (!$connection) {
                    echo "Impossible de se connecter au Raspberry Pi.";
                    exit;
                }

                if (!ssh2_auth_password($connection, $username, $password)) {
                    echo "L'authentification SSH a échoué.";
                    exit;
                }

                $stream = ssh2_exec($connection, "/home/pi/cert.sh");
                stream_set_blocking($stream, true);
                $stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                $stream_out = stream_get_contents($stream_out);

                if ($stream_out === false) {
                    $msg = "Une erreur s'est produite lors de l'exécution des commandes certutil.";
                    include "../modules/error.php";
                } else {
                    $msg = "Les commandes certutil ont été exécutées avec succès.";
                    include "../modules/success.php";
                }
            } else {
                echo "Erreur lors du transfert du fichier sur le Raspberry Pi $raspberryIP.";
            }

            ftp_close($conn_id);
        } else {
            $msg = "Une erreur s'est produite lors du téléchargement du fichier.";
            include "../modules/error.php";
        }
    }
} else {
    $msg = "Aucun fichier n'a été téléchargé.";
    include "../modules/error.php";
}
?>

            <!-- <a tabindex="0" href="<?php echo $_SERVER['HTTP_REFERER']; ?>" class="back-btn"><i
            class="fa-solid fa-arrow-right-from-bracket fa-rotate-180"></i>Retour</a> -->

        </section>
    </div>

</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
