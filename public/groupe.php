<!------------HEADER------------>
<?php
require_once "verif_session.php";
$pageTitle = "Gestion des Groupes"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="group page">

        <?php include "../modules/list_menu.php"; ?>

        <section class="page-content">
            <h1>Liste des groupes</h1>
            <hr>

            <input type="text" id="myInput" class="filter-input" placeholder="Rechercher..."><br>

            <div class="checkbox">
                <input type="checkbox" name="showHosts" id="showHosts">
                <label for="showHosts">
                    Voir les hôtes à ajouter
                </label>
            </div>

            <label for="target_pis">Hôtes Sans Groupe</label>
            <select id="target_pis" name="target_pis[]" multiple>
                <?php
                require_once "../config/controller_config_files.php";
                try {
                    $conn = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $stmt = $conn->query("SELECT pis.ip, pis.name, pg.group_id FROM pis LEFT JOIN pis_groups pg ON pis.id = pg.pi_id");
                    $rows = $stmt->fetchAll();

                    if (count($rows) > 0) {
                        foreach ($rows as $row) {
                            $group_id = $row['group_id'];
                            $data_group_id = ($group_id === null) ? 'null' : $group_id;
                            echo '<option data-group_id="' . $data_group_id . '" value="' . $row['ip'] . '">' . $row['name'] .'</option>';
                        }
                    } else {
                        echo '<option disabled selected>Pas d\'hôte disponible...</option>';
                    }
                } catch (PDOException $e) {
                    echo "Erreur de connexion à la base de données : " . $e->getMessage();
                }
                ?>
            </select>

            <form method="post" action="" id="action-form">
                <table id="myTable">
                    <thead>
                        <tr>
                            <th></th> <!-- Colonne pour la case à cocher -->
                            <th onclick="sortTable(0)">ID</th>
                            <th onclick="sortTable(1)">Nom</th>
                            <th onclick="sortTable(2)">Membres</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Inclure le fichier de configuration de la base de données
                        require_once "../config/controller_config_files.php";

                        try {
                            // Établir la connexion à la base de données
                            $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);

                            // Préparer et exécuter une requête SQL pour récupérer les données des groupes
                            $query = "SELECT groupes.id, groupes.name, COUNT(pis_groups.pi_id) AS membres
                                    FROM groupes
                                    LEFT JOIN pis_groups ON groupes.id = pis_groups.group_id
                                    GROUP BY groupes.id, groupes.name";

                            $stmt = $pdo->query($query);

                            if ($stmt) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<tr>';
                                    echo '<td><input type="checkbox" name="groupIDs[]" value="' . htmlspecialchars($row['id']) . '"></td>';
                                    echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                    echo '<td><a href="javascript:void(0);" onclick="showGroupPis(' . htmlspecialchars($row['id']) . ')">' . htmlspecialchars($row['name']) . '</a></td>';

                                    echo '<td>' . htmlspecialchars($row['membres']) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo "Erreur lors de l'exécution de la requête : " . $pdo->errorInfo()[2];
                            }
                        } catch (PDOException $e) {
                            echo "Erreur de connexion à la base de données : " . $e->getMessage();
                        }

                        ?>
                    </tbody>
                </table>

                <div class="btns-line">
                    <button tabindex="0" id="delete-button" tabindex="0" title="Supprimer les groupes sélectionnés"><i
                            class="fa-solid fa-trash"></i>Supprimer</button>
                    <button tabindex="0" id="push-file-button" tabindex="0" title="Ajouter les fichiers à l'hôte sélectionné"><i
                            class="fa-solid fa-rotate-right"></i>Rafraichir</button>
                    <button tabindex="0" id="reboot-button" tabindex="0" title="Redémarrer les hôtes"><i
                            class="fa-solid fa-power-off"></i>Redémarrer</button>

                    <div class="alert-menu-container">
                        <div class="alert-menu">
                            <button tabindex="0"><i class="fa-solid fa-triangle-exclamation"></i>Alertes <i
                                    id="menu-icon" class="fa-solid fa-chevron-down"></i></button>
                            <div class="dropdown-content">
                                <a id="alertfunction" tabindex="0" href='<?php echo $siteUrl . "public/ppmsAlert.php" ?>' data-option="alerte-ppms">PPMS</a>
                                <a id="alertfunction2" tabindex="0" href='<?php echo $siteUrl . "public/feuAlert.php" ?>'
                                    data-option="alerte-incendie">Incendie</a>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

            <div class="pi-details-container" id="pi-details">
                <!-- Les détails des Hôtes du groupe seront affichés ici. -->
            </div>

        </section>
    </div>
</body>

<script>
    function sortTable(n) {
        var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
        table = document.getElementById("myTable");
        switching = true;
        dir = "asc";

        while (switching) {
            switching = false;
            rows = table.rows;

            for (i = 1; i < (rows.length - 1); i++) {
                shouldSwitch = false;

                x = rows[i].getElementsByTagName("td")[n + 1];
                y = rows[i + 1].getElementsByTagName("td")[n + 1];

                // Extract the text content for comparison
                var xText = x.textContent || x.innerText;
                var yText = y.textContent || y.innerText;

                if (n !== 0) {
                    // Convert to numbers for numeric columns
                    xText = isNaN(xText) ? xText : parseFloat(xText);
                    yText = isNaN(yText) ? yText : parseFloat(yText);
                }

                if (dir === "asc") {
                    if (xText > yText) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (dir === "desc") {
                    if (xText < yText) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }

            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                switchcount++;
            } else {
                if (switchcount === 0 && dir === "asc") {
                    dir = "desc";
                    switching = true;
                }
            }
        }
    }


    // Fonction de filtrage
    document.getElementById("myInput").addEventListener("keyup", function () {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("myInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable");
        tr = table.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) {
            tr[i].style.display = "none";

            for (j = 0; j < tr[i].getElementsByTagName("td").length; j++) {
                td = tr[i].getElementsByTagName("td")[j];

                if (td) {
                    txtValue = td.textContent || td.innerText;

                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        break;
                    }
                }
            }
        }
    });

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

        for (var i = 0; i < options.length; i++) {
            if (checkbox.checked) {
                if (options[i].getAttribute('data-group_id') === 'null') {
                    options[i].style.display = 'block';
                } else {
                    options[i].style.display = 'none';
                }
            } else {
                options[i].style.display = 'block';
            }
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
                        li.textContent = piDetails[i].name + '-' + piDetails[i].ip;
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

    // Détection de la case à cocher et redirection en fonction de l'action choisie
    document.getElementById("delete-button").addEventListener("click", function () {
        // Obtenez tous les éléments cochés
        var checkboxes = document.querySelectorAll("input[name='groupIDs[]']:checked");

        if (checkboxes.length > 0) {
            // S'il y a des éléments cochés, redirigez vers controller_delgroupe.php
            console.log(document.getElementById("action-form").action);
            document.getElementById("action-form").action = "../controllers/controller_delgroupe.php";
            document.getElementById("action-form").submit();
        } else {
            alert("Veuillez sélectionner au moins un groupe à supprimer.");
        }
    });

    document.getElementById("push-file-button").addEventListener("click", function () {
        // Obtenez tous les éléments cochés
        var checkboxes = document.querySelectorAll("input[name='groupIDs[]']:checked");
        //var checkboxes = document.querySelectorAll("input[name='selected_groups[]']:checked");

        if (checkboxes.length > 0) {
            // S'il y a des éléments cochés, redirigez vers envoi.php
            document.getElementById("action-form").action = "envoi.php";
            document.getElementById("action-form").submit();
        } else {
            alert("Veuillez sélectionner au moins un groupe pour pousser des fichiers.");
        }
    });

    document.getElementById("reboot-button").addEventListener("click", function () {
        // Obtenez tous les éléments cochés
        var checkboxes = document.querySelectorAll("input[name='groupIDs[]']:checked");
        //var checkboxes = document.querySelectorAll("input[name='selected_groups[]']:checked");

        if (checkboxes.length > 0) {
            // S'il y a des éléments cochés, redirigez vers envoi.php
            document.getElementById("action-form").action = "reboot_rasp.php";
            document.getElementById("action-form").submit();
        } else {
            alert("Veuillez sélectionner au moins un groupe pour le rédemarrage.");
        }
    });


    document.getElementById("alertfunction").addEventListener("click", function (event) {
        event.preventDefault(); // Empêcher le comportement de lien par défaut
        // var selectedOption = this.value;
        var selectedOption = event.target.getAttribute("data-option");
        // Obtenez tous les éléments cochés
        var checkboxes = document.querySelectorAll("input[name='groupIDs[]']:checked");

        if (checkboxes.length > 0) {
            // S'il y a des éléments cochés, réagissez en conséquence en fonction de l'option sélectionnée
            switch (selectedOption) {
                case "alerte-ppms":
                    //alert("Alerte PPMS");
                    document.getElementById("action-form").action = "ppmsAlert.php";
                    document.getElementById("action-form").submit();

                    //	window.location.href = "alert.php";
                    break;
                case "alerte-incendie":
                   // alert("Alerte Incendie");
		   document.getElementById("action-form").action = "alert.php";
                   document.getElementById("action-form").submit();
                    break;
            }

        } else {
            alert("Veuillez sélectionner au moins un groupe.");
        }
    });


 document.getElementById("alertfunction2").addEventListener("click", function (event) {
        event.preventDefault(); // Empêcher le comportement de lien par défaut
        // var selectedOption = this.value;
        var selectedOption = event.target.getAttribute("data-option");
        // Obtenez tous les éléments cochés
        var checkboxes = document.querySelectorAll("input[name='groupIDs[]']:checked");

        if (checkboxes.length > 0) {
            // S'il y a des éléments cochés, réagissez en conséquence en fonction de l'option sélectionnée
            switch (selectedOption) {
                case "alerte-incendie":
                   // alert("Alerte Incendie");
                   document.getElementById("action-form").action = "feuAlert.php";
                   document.getElementById("action-form").submit();
                    break;
            }

        } else {
            alert("Veuillez sélectionner au moins un groupe.");
        }
    });


    // Fonction pour afficher le bandeau d'alerte
    function showBanner() {
        var alertBanner = document.getElementById('alert-banner');
        alertBanner.style.display = 'block';
    }

    // Appel de la fonction pour afficher le bandeau d'alerte
    showBanner();
</script>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
