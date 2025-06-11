<!------------HEADER------------>
<?php
require_once "verif_session.php";
$pageTitle = "Formulaire d'Information"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="info page">
        <section class="page-content">
            <form id="infoForm" method="post" action="../controllers/controller_infos.php">
                <h1>Informations</h1>

                <hr />

                <label for="titre">Titre <span class="required">*</span></label>
                <input type="text" id="titre" name="titre" required>

                <label for="infos">Information à saisir <span class="required">*</span></label>
                <textarea id="infos" name="infos"></textarea>

                <label for="duration">Durée <span class="required">*</span></label>
                <input type="text" id="duration" name="duration" placeholder="hh:mm:ss"
                    pattern="^([0-1]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$" required oninput="formatDuration(this)">
                <small>Format : hh:mm:ss</small>

                <div class="form-group">
                    <label for="groupid">Sélectionner un groupe <span class="required">*</span></label>
                    <select class="form-control selectpicker" name="group_id[]" id="groupid" multiple
                        data-live-search="true" title="Aucun groupe sélectionné">
                        <?php
                        require_once "../config/controller_config_files.php";
                        $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db, $dbuser, $dbpasswd);

                        // Récupérer tous les groupes depuis la table "groupes"
                        $query = "SELECT id, name FROM groupes";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Générer les options de la liste déroulante
                        foreach ($groups as $group) {
                            echo '<option value="' . $group['id'] . '">' . $group['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <input type="hidden" name="selected_pis" id="selected_pis" value="">
                <input type="hidden" name="existing_group" id="existing_group" value="">

                <button tabindex="0" type="submit" class="submit-btn"><i class="fa-solid fa-floppy-disk"></i>
                    Sauvegarder</button>
            </form>

        </section>
    </div>
</body>

<script>
function formatDuration(input) {
    let inputValue = input.value.replace(/\D/g, ''); // Supprimez tous les caractères non numériques
    let hours = inputValue.substring(0, 2); // Obtenez les deux premiers chiffres pour les heures
    let minutes = inputValue.substring(2, 4); // Obtenez les chiffres suivants pour les minutes
    let seconds = inputValue.substring(4, 6); // Obtenez les chiffres suivants pour les secondes

    // Limitez les heures à deux chiffres
    hours = Math.min(parseInt(hours), 99).toString().padStart(2, '0');

    // Formate la durée avec des deux-points
    let formattedValue = hours + ':' + minutes + ':' + seconds;

    input.value = formattedValue; // Met à jour la valeur du champ d'entrée
}

document.getElementById('infoForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Empêche l'envoi du formulaire par défaut

    // Afficher un message de chargement ou une animation pour indiquer que le traitement est en cours

    // Récupérer les données du formulaire
    var formData = new FormData(this);

    // Envoyer les données via AJAX
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Gérer la réponse du serveur
        if (data.success) {
            // Afficher un message de succès à l'utilisateur
            alert('Informations enregistrées avec succès !');
        } else {
            // Afficher un message d'erreur à l'utilisateur
            alert('Erreur lors de l\'enregistrement des informations : ' + data.message);
        }
    })
    .catch(error => {
        // Gérer les erreurs de la requête AJAX
        console.error('Erreur AJAX:', error);
        alert('Une erreur s\'est produite lors de l\'envoi du formulaire.');
    });
});
</script>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
