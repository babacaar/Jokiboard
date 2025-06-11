<!------------HEADER------------>
<?php
$pageTitle = "Absence"; // Titre de la page
$dropDownMenu = false;
include "../modules/header.php";
?>

<?php
require_once "../config/controller_config_files.php";
$pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
$stmt1 = $pdo->prepare("SELECT * FROM `absence` ORDER BY fin_absence DESC, nom ASC LIMIT 15");
//$stmt1->bindValue(':id', $id);
//$stmt1->bindParam(1, $id);
$stmt1->execute();
$res1 = $stmt1->fetchall();
?>
<!------------BODY------------>

<body>
    <div class="gestion page">
        <section class="page-content">
            <div class="grid-wrapper">
                <div class="one">
                    <h2>Absences du Personnel</h2>
                    <hr>
                    <table>
                        <tr>
                            <th>NOM Prénom</th>
                            <th>Motif</th>
                            <th>Date de Début</th>
                            <th>Date de Fin</th>
                        </tr>

                        <?php
                        date_default_timezone_set('Europe/Paris');

                        foreach ($res1 as $row1) {
                            $name = $row1['nom'];
                            $fname = $row1['prenom'];
                            $motif = $row1['motif'];
                            $dateDebut = date('d/m/Y', strtotime($row1['debut_absence']));
                            $dateFin = date('d/m/Y', strtotime($row1['fin_absence']));
                            $id = $row1['id_absence']; // Ajout de la variable $id
                            // Conversion des dates de début et de fin en timestamp
                            $dateFinTimestamp = strtotime($row1['fin_absence']);
                            $dateActuelle = strtotime(date('Y-m-d')); // Date actuelle
                            // Vérifie si la date de fin est postérieure à la date actuelle
                            if ($dateFinTimestamp >= $dateActuelle) {
                            echo "<tr data-id='$id'>
                                 <td>" . $name . " " . $fname . "</td>
                                 <td>" . $motif . "</td>
                                 <td>" . $dateDebut . "</td>
                                 <td class='date-fin'>" . $dateFin . "</td>
                                 <td>
                                 <button class='btn btn-primary modifier-btn' data-id='$id'>Modifier</button>
                                 </td>

                                 <td>
                                   <form method='post' action='../controllers/controller_delete_absence.php'>
                                   <input type='hidden' name='id' value='$id'>
                                   <button type='submit' name='supprimer'>Supprimer</button>
                                   </form>
                                 </td>
                                 </tr>";
                            }
                        }
                        ?>

                    </table>

                    <div id="formulaire-modification" style="display: none;">
                        <h3>Modifier Absence</h3>
                        <form id="modifierForm" method="post" action="../controllers/controller_modify_absence.php">
                          <input type="hidden" name="id" id="absenceId">
                          <div>
                             <label for="nouvelleDateFin">Nouvelle Date de Fin</label>
                             <input type="date" id="nouvelleDateFin" name="nouvelleDateFin" required>
                          </div>
                          <button type="submit" class="btn btn-primary" name="modifier">Modifier</button>
                        </form>
                    </div>

                </div>
            </div>
        </section>
    </div>
<script>
    // Ajoute un gestionnaire d'événements pour chaque bouton "Modifier"
    document.querySelectorAll('.modifier-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Récupère l'identifiant de l'absence à partir de l'attribut data-id
            const id = this.getAttribute('data-id');
            // Charge les détails de l'absence dans le formulaire de modification
            const dateFin = document.querySelector(`tr[data-id="${id}"] .date-fin`).innerText;
            document.getElementById('nouvelleDateFin').value = dateFin;
            document.getElementById('absenceId').value = id;
            // Affiche le formulaire de modification
            document.getElementById('formulaire-modification').style.display = 'block';
        });
    });
</script>


</body>
