<!------------HEADER------------>
<?php
$pageTitle = "Configuration des menus"; // Titre de la page
$dropDownMenu = false;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
  <div class="gestion page">
    <section class="page-content">
        <?php
        require_once "../config/controller_config_files.php";
        // Définir le répertoire de destination
        $destination_dir = "$Url";

        // Vérifier si le fichier a été téléchargé
        if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {

          // Définir le nom du fichier temporaire
          $temp_file = $_FILES['pdf_file']['tmp_name'];

          // Générer un nom unique pour l'image
          $image_name = "menupeda.jpg";

          // Convertir le PDF en image
          $command = "gm convert -density 700 -resize 1920x1080! -quality 95 " . $temp_file . " " . $destination_dir . $image_name;
          exec($command);

          // Afficher un message de succès
          echo "<p>Le fichier PDF a été converti en image et enregistré.</p>";

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
