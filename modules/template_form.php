<?php
$pageTitle = "Formulaire"; // Titre de la page
include "modules/header.php";
?>

<body>
    <div class="page">
        <button class="back-btn" onclick="window.location.href = 'http://localhost/menu.php';" value="Menu">
            <i class="fa-solid fa-arrow-right-from-bracket fa-rotate-180"></i>
        </button>

        <section class="page-content">
            <form method="post" action="">
                <h2>Formulaire</h2>
                <hr />

                <label for="titre">TITRE</label>
                <input type="text" id="titre" name="titre" required>

                <label for="text">Texte</label>
                <textarea id="text" name="text" required></textarea>

                <label for="displayDateTime">Date et heure</label>
                <input type="datetime-local" id="displayDateTime" name="displayDateTime" required>

                <label for="selection" name="selection">Sélection</label>
                <select id="selection" name="selection">
                    <option value="Non défini" selected>Non défini</option>
                    <option value="OUI">OUI</option>
                    <option value="NON">NON</option>
                </select>

                <button tabindex="0" type="submit" class="submit-btn"><i class="fa-solid fa-floppy-disk"></i> Sauvegarder</button>
            </form>
        </section>
    </div>

</body>

</html>