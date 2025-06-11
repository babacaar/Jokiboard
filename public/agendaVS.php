<!------------HEADER------------>
<?php
$pageTitle = "Calendrier Vie Scolaire";
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>
<body>
    <div class="gestion page gestion-calendrier">
        <section class="page-content-calendrier">
            <h1>Gestion des calendriers</h1>
            <hr>

            <!-- Conteneur Flex pour mettre les deux sections côte à côte -->
            <div class="flex-container">

                <!-- Formulaire d'ajout -->
                <div class="form-container">
                    <h2>Ajouter un calendrier</h2>
                    <form action="../controllers/controller_agendaVS.php" method="post">
                        <input type="hidden" name="action" value="add">
                        <label for="url">Lien de l'agenda <span class="required">*</span>
                        <span class="info-icon" data-tooltip="Entrez le lien de partage du calendrier ici">?</span>
                        </label>
                        <input type="text" class="form-control" name="url" required><br><br>

                        <label for="calendarTitle">Titre du calendrier <span class="required">*</span>
                        <span class="info-icon" data-tooltip="Indiquez un nom pour identifier ce calendrier (Ex: Calendrier conseil de classe)">?</span>
                        </label>
                        <input type="text" class="form-control" name="calendarTitle" required><br><br>

                        <label>Type d'agenda <span class="required">*</span>
                        <span class="info-icon" data-tooltip="Sélectionnez si le calendrier provient de Google ou Outlook">?</span>
                        </label><br>
                        <input type="radio" name="agendaType" id="agenda-type-google" value="Google" required>
                        <label for="agenda-type-google">Google</label><br>
                        <input type="radio" name="agendaType" id="agenda-type-outlook" value="Outlook" required>
                        <label for="agenda-type-outlook">Outlook</label><br><br>

                        <button class="submit-btn" type="submit">Ajouter</button>
                    </form>
                </div>

                <!-- Séparateur vertical -->
                <div class="separator"></div>

                <!-- Affichage des calendriers existants -->
            <?php
            require_once "../config/controller_config_files.php";
            try {
                $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);

                // Récupérer toutes les entrées existantes
                $agendas = $pdo->query("SELECT * FROM agendaVS")->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo "<p style='color: red;'>Erreur lors de la récupération des calendriers : " . $e->getMessage() . "</p>";
                $agendas = [];
            }
            ?>
                <!-- Liste des calendriers -->
                <div class="list-container">
                    <h2>Liste des calendriers existants</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Lien</th>
                                <th>Titre</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agendas as $agenda) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($agenda['url']); ?></td>
                                    <td><?= htmlspecialchars($agenda['calendarTitle']); ?></td>
                                    <td><?= htmlspecialchars($agenda['agendaType']); ?></td>
                                    <td>
                                        <div class="button-container">
                                            <form action="../controllers/controller_agendaVS.php" method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="id" value="<?= $agenda['id']; ?>">
                                                <button type="submit"><i class="fa-solid fa-pencil"></i>Modifier</button>
                                            </form>
                                            <form action="../controllers/controller_agendaVS.php" method="post" style="display:inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $agenda['id']; ?>">
                                                <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce calendrier ?');"><i class="fa-solid fa-trash"></i>Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div> <!-- Fin du conteneur flex -->
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
