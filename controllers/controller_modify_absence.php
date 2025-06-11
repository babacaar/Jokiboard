<!------------HEADER------------>
<?php
$pageTitle = "Suppression"; // Titre de la page
$dropDownMenu = false;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="controller page">
        <section class="page-content">
<?php
require_once "../config/controller_config_files.php";
$pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);

// Vérifie si le formulaire a été soumis et si les données nécessaires sont présentes
if (isset($_POST['modifier']) && isset($_POST['id']) && isset($_POST['nouvelleDateFin'])) {
    // Récupère les données du formulaire
    $id = $_POST['id'];
    $nouvelleDateFin = $_POST['nouvelleDateFin'];

    // Effectue la mise à jour dans la base de données
    $stmt = $pdo->prepare("UPDATE `absence` SET fin_absence = :nouvelleDateFin WHERE id_absence = :id");
    $stmt->execute(array(':nouvelleDateFin' => $nouvelleDateFin, ':id' => $id));

    // Redirige l'utilisateur vers la page principale après la modification
    //header("Location: index.php");
    //exit(); // Assure que le script s'arrête après la redirection
    $msg = "Modif réussie !!!";
    include "../modules/success.php";
} else {
    // Si les données nécessaires ne sont pas présentes, redirige l'utilisateur vers la page principale avec un message d'erreur
    //header("Location: index.php?error=missing_data");
    //exit();
    $msg = "Modif échoué :(";
    include "./modules/error.php";
}
?>


        </section>
    </div>
</body>
