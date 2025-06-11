<!------------HEADER------------>
<?php
$pageTitle = "Gestion des vidéos"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="page">

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

            <label for="target_pis">Hôtes Sans Groupe :</label>
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
                        echo '<option disabled selected>OUPS pas de Raspberry disponible 😞</option>';
                    }
                } catch (PDOException $e) {
                    echo "Erreur de connexion à la base de données : " . $e->getMessage();
                }
                ?>
            </select>
            <br>
            <form method="post" action="upload.php" id="action-form" enctype="multipart/form-data">
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
                <label for="videoFile">Sélectionner un fichier vidéo :</label>
                <input type='file' name='video' id='videoFile' accept='video'>
		<button type="submit" name="submit" id="upload" class="button">Upload</button>
            </form>
            <div class="pi-details-container" id="pi-details">
                <!-- Les détails des Raspberry Pi du groupe seront affichés ici. -->
            </div>

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

                            x = rows[i].getElementsByTagName("td")[n];
                            y = rows[i + 1].getElementsByTagName("td")[n];

                            if (dir === "asc") {
                                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                                    shouldSwitch = true;
                                    break;
                                }
                            } else if (dir === "desc") {
                                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
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
                                    li.textContent = piDetails[i].name + ' - ' + piDetails[i].ip;
                                    ul.appendChild(li);
                                }

                                // Créez un div conteneur pour les détails et ajoutez-lui la classe personnalisée
                                var detailsContainer = document.createElement('div');
                                detailsContainer.className = 'pi-details-container';
                                detailsContainer.appendChild(ul);

                                detailsDiv.appendChild(detailsContainer);
                            } else {
                                detailsDiv.textContent = 'Aucun Raspberry Pi dans ce groupe.';
                            }
                        }
                    };
                    xhr.send();
                }


                function uploadVideo() {
                    // Obtenez tous les éléments cochés
                    var checkboxes = document.querySelectorAll("input[name='groupIDs[]']:checked");

                    // Obtenez le fichier vidéo sélectionné
                    var videoFile = document.getElementById("videoFile").files[0];

                    if (checkboxes.length > 0 && videoFile) {
                        // Créez un objet FormData
                        var formData = new FormData();

                        // Ajoutez le fichier vidéo au FormData avec la clé "video"
                        formData.append('video', videoFile);

                        // Ajoutez les groupIDs sélectionnés au FormData
                        checkboxes.forEach(function (checkbox) {
                            formData.append('groupIDs[]', checkbox.value);
                        });

			// Afficher le spinner avant le téléversement
        		var spinner = document.createElement('div');
        		spinner.classList.add('spinner'); // Ajouter la classe CSS pour l'animation du spinner
        		document.body.appendChild(spinner);

                        // Effectuez la requête AJAX pour envoyer le FormData
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'upload.php', true);

                        xhr.upload.addEventListener('progress', function (event) {
                            if (event.lengthComputable) {
                                var percent = (event.loaded / event.total) * 100;
                                console.log('Upload en cours : ' + percent.toFixed(2) + '%');
                            }
                        });

                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === XMLHttpRequest.DONE) {
                                if (xhr.status === 200) {
                                    console.log('Upload terminé avec succès.');
				    spinner.remove(); // Supprimer le spinner
              			    alert("Succès ! La vidéo a été téléversée avec succès."); // Afficher un message de réussite
                                    // Traitez la réponse côté serveur si nécessaire
                                } else {
                                    console.error('Erreur lors de l\'upload : ' + xhr.status);
				    spinner.remove(); // Supprimer le spinner
				    alert("Erreur ! Le téléversement de la vidéo a échoué : " + xhr.responseText.split(": ")[1]); // Afficher un message d'erreur provenant du serveur
                                }
                            }
                        };

                        // Envoyez le FormData avec la requête
                        xhr.send(formData);
                    } else {
                        alert("Veuillez sélectionner au moins un groupe et un fichier vidéo.");
                    }
                }
            </script>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
