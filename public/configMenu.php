<!------------HEADER------------>
<?php
require_once "verif_session.php";
$pageTitle = "Informations"; // Titre de la page
$dropDownMenu = true;
include "../modules/header.php";
?>
<!------------BODY------------>

<body>
    <div class="absences page">
        <section class="page-content">
        	<div class="container">

		<h1>Configuration des menus/images</h1>
            	<hr>

        		<p>Pour convertir et ajouter le menu du self, cliquez <a href="config_menuself.php">ici</a>.</p>

        		<p>Pour convertir et ajouter le menu du restaurant pédagogique, cliquez <a href="config_menupeda.php">ici</a>.</p>

        		<p>Pour convertir et/ou ajouter des images temporairement, cliquez <a href="config_images.php">ici</a>.</p>

        	</div>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
