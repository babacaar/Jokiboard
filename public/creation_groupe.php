<!------------HEADER------------>
<?php
$pageTitle = "Nouveau Groupe"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>

<!------------BODY------------>

<body>

    <div class="group-creation page">

        <?php include "../modules/list_menu.php"; ?>

        <section class="page-content">
            <h1 class="title">Nouveau groupe</h1>
            <hr>

            <form method="post" action="../controllers/controller_ajoutgroupe.php">
                <div class="form-group">
                    <label for="name">Nom de groupe <span class="required">*</span></label>
                    <input type="text" name="name" id="name" required="">
                </div>
                <div class="form-group">
                    <label for="description">Description du groupe</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <div class="form-group">
                    <button tabindex="0" type="submit" class="submit-btn" name="add" id="add">Ajouter</button>
                </div>
            </form>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>