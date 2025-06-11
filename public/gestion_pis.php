<!------------HEADER------------>
<?php
$pageTitle = "Gestion des Raspberrys Pi"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="pis-gestion page">
        <?php include "../modules/list_menu.php"; ?>

        <section class="page-content">
            <h1>Nouvel Hôte</h1>
            <form method="post" action="">
                <label for="name">Nom de l'hôte <span class="required">*</span></label>
                <input type="text" id="name" name="name" required>

                <label for="ip">Adresse IP <span class="required">*</span></label>
                <input type="text" id="ip" name="ip" required>

		<label for="video_acceptance">Accepte les vidéos ? <span class="required">*</span></label><br>

		<input type="radio" id="accept_yes" name="video_acceptance" value="OUI" required>
		<label for="accept_yes">Oui</label><br>

		<input type="radio" id="accept_no" name="video_acceptance" value="NON" required>
		<label for="accept_no">Non</label><br><br>


                <div class="checkbox">
                    <input type="checkbox" name="showHosts" id="showHosts">
                    <label for="showHosts">
                        Voir les hôtes existants
                    </label>
                </div>

                <label for="target_pis">Hôtes Existants</label>
                <select id="target_pis" name="target_pis[]" multiple>
                    <?php
                    require_once "../config/controller_config_files.php";
                    try {
                        $conn = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        $stmt = $conn->query('SELECT * FROM pis');
                        $rows = $stmt->fetchAll();

                        if (count($rows) > 0) {
                            foreach ($rows as $row) {
                                $group_id = $row['group_id'];
                                $data_group_id = ($group_id === null) ? 'null' : $group_id;
                                echo '<option data-group_id="' . $data_group_id . '" value="' . $row['ip'] . '">' . $row['name'] . ' - ' . $row['ip'] . '</option>';
                            }
                        } else {
                            echo '<option disabled selected>Pas d\'hôte disponible...</option>';
                        }
                    } catch (PDOException $e) {
                        echo "Erreur de connexion à la base de données : " . $e->getMessage();
                    }
                    ?>
                </select>

                <label for="username">Nom d'utilisateur SSH <span class="required">*</span></label>
                <input type="text" id="username" name="username" required>

                <label for="password">Mot de passe SSH <span class="required">*</span></label>
                <input type="password" id="password" name="password" required>

                <button class="submit-btn" type="submit" name="submit"><i class="fa-solid fa-floppy-disk"></i> Sauvegarder</button>
            </form>

            <?php
            require_once "../config/controller_config_files.php";
            if (isset($_POST['submit'])) {
                try {
                    // Connexion à la base de données
                    $conn = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Récupération des données du formulaire
                    $name = $_POST['name'];
                    $ip = $_POST['ip'];
                    $username = $_POST['username'];
                    $password = $_POST['password'];
		    $videoAcceptance = ($_POST['video_acceptance'] == "OUI") ? 1 : 0; // Convertir "oui" en 1 et "non" en 0
                    // $target_pis = implode(',', $_POST['target_pis']); // Si vous souhaitez stocker les IP sous forme de chaîne

                    // Requête d'insertion dans la table
                    $stmt = $conn->prepare("INSERT INTO pis (name, ip, username, password, video_acceptance) VALUES (:name, :ip, :username, :password, :video_acceptance)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':ip', $ip);
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':password', $password);
		    $stmt->bindParam(':video_acceptance', $videoAcceptance, PDO::PARAM_INT);
                    //$stmt->bindParam(':target_pis', $target_pis);

                    // Exécution de la requête
                    $stmt->execute();

                    echo "Données insérées avec succès dans la base de données.";
                } catch (PDOException $e) {
                    echo "Erreur d'insertion dans la base de données : " . $e->getMessage();
                }
            }
            ?>

        </section>

        <script>
            function filterPis() {
                var checkbox = document.getElementById("showHosts");
                var label = document.querySelector("label[for='target_pis']");
                var select = document.getElementById("target_pis");
                var options = select.getElementsByTagName('option');

                if (checkbox.checked) {
                    label.style.display = "block";
                    select.style.display = "block";
                } else {
                    label.style.display = "none";
                    select.style.display = "none";
                }
            }

            document.getElementById("showHosts").addEventListener("change", filterPis);

            filterPis(); // Appel initial pour configurer l'état initial

            function showGroupPis(groupId) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_pis.php?group_id=' + groupId, true);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var piDetails = JSON.parse(xhr.responseText);
                        var detailsDiv = document.getElementById('pi-details');
                        detailsDiv.innerHTML = ''; // Effacez le contenu précédent

                        if (piDetails.length > 0) {
                            var ul = document.createElement('ul');
                            for (var i = 0; i < piDetails.length; i++) {
                                var li = document.createElement('li');
                                li.textContent = piDetails[i].name + ' - ' + piDetails[i].ip;
                                ul.appendChild(li);
                            }

                            // Créez un div conteneur pour les détails et ajoutez-lui la classe personnalisée
                            var detailsContainer = document.createElement('div');
                            detailsContainer.className = 'pi-details-container';
                            detailsContainer.appendChild(ul);

                            detailsDiv.appendChild(detailsContainer);
                        } else {
                            detailsDiv.textContent = 'Aucun Hôte dans ce groupe.';
                        }
                    }
                };
                xhr.send();
            }

        </script>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
