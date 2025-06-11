<!------------HEADER------------>
<?php
$pageTitle = "Configuration"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="controller page">
        <section class="page-content">

            <?php require_once "../config/controller_config_files.php"; ?>

            <?php
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $motif = $_POST['motif'];
            $commentaire = $_POST['commentaire'];
            $debutabs = $_POST['debut_absence'];
            $finabs = $_POST['fin_absence'];

            $msg = "";

            try {
                $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);

                $stmt = $pdo->prepare("INSERT INTO absence (nom, prenom, motif, commentaire, debut_absence, fin_absence) VALUES (:nom, :prenom, :motif, :commentaire, :debut_absence, :fin_absence)");
                $stmt->bindParam(':nom', $nom);
                $stmt->bindParam(':prenom', $prenom);
                $stmt->bindParam(':motif', $motif);
                $stmt->bindParam(':commentaire', $commentaire);
                $stmt->bindParam(':debut_absence', $debutabs);
                $stmt->bindParam(':fin_absence', $finabs);
                $stmt->execute();

                $msg .= "L'absence de " . $_POST['nom'] . " " . $_POST['prenom'] . " du " . $_POST['debut_absence'] . " au " . $_POST['fin_absence'] . " pour le motif " . $_POST['motif'] . " a été ajoutée avec succès.";
                include "../modules/success.php";

            } catch (PDOException $e) {
                $msg .= "Erreur d'insertion : " . $e->getMessage();
                include "../modules/error.php";
            } catch (Exception $e) {
                $msg .= $e->getMessage();
                include "../modules/error.php";
            }
            $pdo = null;
            ?>

        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>