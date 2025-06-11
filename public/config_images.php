<!------------HEADER------------>
<?php
$pageTitle = "Configuration de menus"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="gestion page">
        <section class="page-content">

            <div class="container">
                <form id="file-upload-form" action="../controllers/controller_image_convert.php" method="post" enctype="multipart/form-data">
                    <h1>Conversion & Redimensionnement</h1>
                    <hr>

                    <label for="pdf_file">Fichier PDF <span class="required">*</span></label>
                    <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf" required>

                    <label for="file_name">Nom du fichier converti : </label>
                    <input type="text" class="form-control" id="file_name" name="file_name" required>

                    <button class="submit-btn" type="submit" id="convert-button">
                         Convertir
                    </button>

                    <div id="loading-spinner" class="d-none">
                        <i class="fa fa-spinner fa-spin"></i> Conversion en cours...
                    </div>

                    <p class="message"></p>
                </form>
            </div>
        </section>
    </div>

    <script>
            <?php
                require_once "../config/controller_config_files.php"; // pour $dbhost
            ?>
            const dbhost = <?php echo json_encode($dbhost); ?>;
            document.addEventListener("DOMContentLoaded", function () {
            var form = document.getElementById('file-upload-form');
            var loadingSpinner = document.getElementById('loading-spinner');
            var message = document.querySelector('.message');

            form.addEventListener('submit', function (event) {
                event.preventDefault(); // Empêche le formulaire de se soumettre normalement

                // Affiche le spinner de chargement et cache le message
                loadingSpinner.classList.remove('d-none');
                message.textContent = '';

                // Crée une instance de l'objet FormData pour envoyer les données du formulaire
                var formData = new FormData(form);

                // Récupère la valeur du champ de saisie du nom du fichier
                var fileName = document.getElementById('file_name').value;

                // Ajoute le nom du fichier aux données du formulaire
                formData.append('file_name', fileName);

                // Envoie le formulaire en utilisant AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        // Si la réponse est reçue avec succès
                        loadingSpinner.classList.add('d-none'); // Cache le spinner de chargement
                        message.textContent = 'Fichier converti avec succès!'; // Affiche un message de succès
                        message.style.color = 'green'; // Couleur verte pour le message de succès

                        // Ajouter du texte distinct en dessous du message de succès
                        var additionalText = document.createElement('p');
                        additionalText.textContent = 'Lien pour afficher les images: http://' + dbhost + '/' + fileName + '.jpg';
                        additionalText.style.color = '#ff721f';
                        message.parentNode.appendChild(additionalText);

                        } else {
                        // En cas d'erreur
                        loadingSpinner.classList.add('d-none'); // Cache le spinner de chargement
                        message.textContent = 'Une erreur est survenue lors de la conversion.'; // Affiche un message d'erreur
                        message.style.color = 'red'; // Couleur rouge pour le message d'erreur
                    }
                };
                xhr.send(formData);
            });
        });
    </script>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
