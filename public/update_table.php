<?php
$pageTitle = "Modifier Table";
$dropDownMenu = true;
include "../modules/header.php";
?>
<body>
    <div class="page">
        <section class="page-content">
            <?php
            require_once "../config/controller_config_files.php";

            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $selectedPis = isset($_POST['selected_pis']) ? $_POST['selected_pis'] : '';
                $existingGroups = isset($_POST['existing_group']) ? $_POST['existing_group'] : '';

                // Validation des données
                $selectedPisArray = explode(',', $selectedPis);
                $existingGroupsArray = explode(',', $existingGroups);

                try {
                    $conn = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Supprimer les relations existantes pour les Pi sélectionnés
                    $deleteQuery = "DELETE FROM pis_groups WHERE pi_id IN (" . implode(',', array_map('intval', $selectedPisArray)) . ")";
                    $conn->exec($deleteQuery);

                    // Insérer les nouvelles relations
                    foreach ($selectedPisArray as $pi_id) {
                        foreach ($existingGroupsArray as $group_id) {
                            $insertQuery = "INSERT INTO pis_groups (pi_id, group_id) VALUES (:pi_id, :group_id)";
                            $stmt = $conn->prepare($insertQuery);
                            $stmt->bindParam(':pi_id', $pi_id, PDO::PARAM_INT);
                            $stmt->bindParam(':group_id', $group_id, PDO::PARAM_INT);
                            if (!$stmt->execute()) {
                                $msg = "Erreur lors de la mise à jour de la base de données : " . implode(", ", $stmt->errorInfo());
                                include "../modules/error.php";
                                exit;
                            }
                        }
                    }

                    // Mise à jour réussie, maintenant mettez à jour la colonne "membre" dans la table "groupes".
                    $updateGroupMemberCountQuery = "UPDATE groupes g
                                                    SET g.membres = (SELECT COUNT(pg.pi_id) FROM pis_groups pg WHERE pg.group_id = g.id)";
                    $conn->exec($updateGroupMemberCountQuery);

                    $msg = "Les hôtes ont bien été ajoutés aux groupes sélectionnés.";
                    include "../modules/success.php";

                } catch (PDOException $e) {
                    $msg = "Erreur de connexion à la base de données : " . $e->getMessage();
                    include "../modules/error.php";
                }
            } else {
                $msg = "Accès non autorisé.";
                include "../modules/error.php";
            }
            ?>
        </section>
    </div>
</body>
<?php include "../modules/footer.php"; ?>
