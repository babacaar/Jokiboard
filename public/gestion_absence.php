<!------------HEADER------------>
<?php
require_once "verif_session.php";
$pageTitle = "Gestion des Absences"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
  <div class="gestion page">
     <?php include "../modules/absences_menu.php"; ?>
    <section class="page-content">

      <div class="container">
        <form action="../controllers/controller_absences.php" method="post">
          <h1>Saisie d'une absence</h1>
          <hr>

          <label for="nom">Nom <span class="required">*</span></label>
          <input type="text" class="form-control" name="nom" required style="text-transform: capitalize;">

          <label for="prenom">Prénom <span class="required">*</span></label>
          <input type="text" class="form-control" name="prenom" required style="text-transform: capitalize;">

         <!-- <label for="motif">Motif</label> -->
       <!--  <select name="motif" class="form-control">
  	  <option value="Maladie">Maladie</option>
  	  <option value="Enfant malade">Enfant malade</option>
  	  <option value="Personnel">Personnel</option>
  	  <option value="Formation">Formation</option>
  	  <option value="Convocation">Convocation</option>
	  </select> -->

          <label for="commentaire">Commentaire</label>
          <input type="text" class="form-control" name="commentaire" maxlength="100">

          <label for="debut_absence">Date début d'absence <span class="required">*</span></label>
          <input type="date" class="form-control" name="debut_absence" required>

          <label for="fin_absence">Date fin d'absence <span class="required">*</span></label>
          <input type="date" class="form-control" name="fin_absence" required>

          <button class="submit-btn" type="submit" class="btn btn-primary">Envoyer</button>

        </form>
      </div>


      <script>
        // Récupérer l'élément déclencheur et le conteneur
        const settingsButton = document.getElementById("settings-button");
        const settingsContainer = document.getElementById("settings-container");
        function toggleSettings() {
          var settingsContainer = document.querySelector('.settings-container');
          if (settingsContainer.style.display === 'none' || settingsContainer.style.display === '') {
            settingsContainer.style.display = 'block'; // Afficher les boutons paramètres
          } else {
            settingsContainer.style.display = 'none'; // Masquer les boutons paramètres
          }
        }
      </script>
    </section>
  </div>
</body>


<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
