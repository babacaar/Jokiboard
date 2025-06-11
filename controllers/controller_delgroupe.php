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

            <?php
            try {
                // Inclure le fichier de configuration de la base de données
                require_once "../config/controller_config_files.php";

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['groupIDs']) && is_array($_POST['groupIDs'])) {
                    // Établir la connexion à la base de données
                    $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Récupérer les IDs à supprimer
                    $groupIDs = $_POST['groupIDs'];
                    $placeholders = implode(',', array_fill(0, count($groupIDs), '?'));

                    // Supprimer d'abord les dépendances dans pis_groups
                    $deleteDependenciesSQL = "DELETE FROM pis_groups WHERE group_id IN ($placeholders)";
                    $deleteDependenciesStmt = $pdo->prepare($deleteDependenciesSQL);
                    $deleteDependenciesStmt->execute($groupIDs);

                    // Supprimer les groupes sélectionnés
                    $deleteGroupsSQL = "DELETE FROM groupes WHERE id IN ($placeholders)";
                    $deleteGroupsStmt = $pdo->prepare($deleteGroupsSQL);
                    $deleteGroupsStmt->execute($groupIDs);

                    $msg = "Les groupes sélectionnés ont bien été supprimés.";
                    include "../modules/success.php";
                }
            } catch (PDOException $e) {
                $msg = "Erreur de base de données : " . $e->getMessage();
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
