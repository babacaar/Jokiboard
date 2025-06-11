<!------------HEADER------------>
<?php
$pageTitle = "Formulaire en PHP/MySQL"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>
    <div class="create-task page">
        <section class="page-content">
            <form method="post" action="../controllers/controller_creation.php">
                <h2>Créer Nouvelle Tâche</h2>

                <label for="TDL_Name_Tache">Tâche :</label>
                <input type="text" name="TDL_Name_Tache" placeholder="Entrez la tâche à effectuer" size="30" />

                <label for="TDL_Priority" name="TDL_Priority">Priorité :</label>
                <select id="TDL_Priority" name="TDL_Priority">
                    <option value="Moyenne">Moyenne</option>
                    <option value="Haute">Haute</option>
                    <option value="Basse">Basse</option>
                </select>

                <label for="TDL_Date_Jalon">A faire pour le : </label>
                <input type="date" name="TDL_Date_Jalon" value="">

                <button tabindex="0" type="submit" class="submit-btn">Sauvegarder</button>
            </form>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>