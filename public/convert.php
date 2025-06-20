<!------------HEADER------------>
<?php
$pageTitle = "Configuration des menus"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
  <div class="gestion page">
    <section class="page-content">
        <!-- Spinner -->
    <div id="spinner">
        <img src="spinner.gif" alt="Spinner">
    </div>

    <!-- Script JavaScript -->
    <script>
        // Affiche le spinner pendant l'exécution de la tâche
        function showSpinner() {
            document.getElementById("spinner").style.display = "block";
        }

        // Masque le spinner une fois la tâche terminée
        function hideSpinner() {
            document.getElementById("spinner").style.display = "none";
        }
    </script>

        <?php
        require_once "../config/controller_config_files.php";
        // Définir le répertoire de destination
        $destination_dir = "$dbhost/public/";

        // Vérifier si le fichier a été téléchargé
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {

            echo '<script>showSpinner();</script>';

          // Définir le nom du fichier temporaire
          $temp_file = $_FILES['pdf_file']['tmp_name'];

          // Générer un nom unique pour l'image
          $image_name = "menu.jpg";

          // Convertir le PDF en image
          $command = "gm convert -density 700 -resize 1920x1080 -quality 95 " . $temp_file . " " . $destination_dir . $image_name;
          exec($command);

          // Appel de la fonction hideSpinner() une fois la commande terminée
            echo '<script>hideSpinner();</script>';
          // Afficher un message de succès
          echo "<p>Le fichier PDF a été converti en image et enregistré.</p>";
          // Redirection vers testV.php
          //header("Location: testV.php");
          //exit;
        } else {

          // Afficher un message d'erreur
          echo "<p>Une erreur est survenue lors du téléchargement du fichier.</p>";

        }

        ?>
    </section>
  </div>
</body>


<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
