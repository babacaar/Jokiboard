<?php
require_once 'verif_session.php';
require_once '../Fonctions/fonction.php';

$pageTitle = "Liste des Rasperrys Pi";
$dropDownMenu = true;
include "../modules/header.php";
?>
<body>
    <div class="list page">
        <?php include "../modules/list_menu.php"; ?>
        <section class="page-content">
            <h1>Liste des Hôtes</h1>
            <hr>
            <label for="target_pis">Hôtes cibles</label>
            <select id="target_pis" name="target_pis[]" multiple>
                <?php
                require_once "../config/controller_config_files.php";
                try {
                    $conn = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $conn->query('SELECT * FROM pis');
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($rows) > 0) {
                        foreach ($rows as $row) {
                            echo '<option value="' . $row['id'] . '">' . $row['name'] . ' - ' . $row['ip'] . '</option>';
                        }
                    } else {
                        echo '<option disabled selected>Pas d\'hôte disponible...</option>';
                    }
                } catch (PDOException $e) {
                    echo "Erreur de connexion à la base de données : " . $e->getMessage();
                }
                ?>
            </select>
            <?php
            // Récupérer tous les groupes depuis la table "groupes"
            $query = "SELECT id, name FROM groupes";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="selected-group">
                <form method="post" action="update_table.php">
                    <label for="group_id">Sélectionner des groupes</label>
                    <select class="gid" name="group_id[]" id="groupid" multiple>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?php echo $group['id']; ?>">
                                <?php echo $group['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="selected_pis" id="selected_pis" value="">
                    <input type="hidden" name="existing_group" id="existing_group" value="">
                    <button tabindex="0" type="submit" class="submit-btn">Ajouter</button>
                </form>
            </div>
        </section>
    </div>
    <script>
        document.getElementById("target_pis").addEventListener("change", function () {
            var selectedPis = Array.from(this.selectedOptions).map(option => option.value);
            document.getElementById("selected_pis").value = selectedPis.join(',');
            var selectedGroup = document.querySelector(".selected-group");
            selectedGroup.style.display = selectedPis.length > 0 ? "block" : "none";
        });

        document.getElementById("groupid").addEventListener("change", function () {
            var selectedGroups = Array.from(this.selectedOptions).map(option => option.value);
            document.getElementById("existing_group").value = selectedGroups.join(',');
        });
    </script>
    <?php include "../modules/footer.php"; ?>
</body>
