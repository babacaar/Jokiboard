<!------------HEADER------------>
<?php
require_once "verif_session.php";

$pageTitle = "Menu"; // Titre de la page
$dropDownMenu = false;
include "../modules/header.php";
include "../modules/header_settings.php";
?>

<!------------BODY------------>

<body>
    <div class="menu page">
        <section class="page-content">
            <div class="menu-container">

                <h1>Tableau de bord</h1>
                <hr>

                <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/configuration.php" ?>'>Affichage
                    Actuel</a>
                <span class="description">Permet de configurer les URL des affichages.</span>
                <hr>

                <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/gestion_absence.php" ?>'>
                    Gestion des Absences</a>
                <span class="description">Permet d'afficher les tâches qui doivent être effectuées en précisant l'ordre
                    de priorité.</span>
                <hr>

                <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/groupe.php" ?>'>Gestion des
                    Affichages</a>
                <span class="description">Permet de créer des affichages (groupes d'hôtes).</span>
                <hr>

                <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/list.php" ?>'>Configuration
                    des Hôtes/Groupes</a>
                <span class="description">Permet de configurer les hôtes et groupes d'hôtes.</span>
                <hr>

                <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/info.php" ?>'>Informations
                    Ponctuelles</a>
                <span class="description">Permet d'ajouter une information ponctuelle à afficher.</span>
                <hr>

                <!-- <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/upload_video.php" ?>'>
                    Charger Vidéos</a>
                <span class="description">Permet d'ajouter des vidéos.</span>
                <hr> -->

                <a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/custom_style.php" ?>'>
                    <i class="fa-solid fa-paintbrush"></i> Changer Style</a>
                <span class="description">Permet de modifier le style du site.</span>
		<hr>

		<a tabindex="0" class="link-btn" href='<?php echo $siteUrl."/configMenu.php" ?>'>
                    <i class="fa-solid fa-gear"></i> Configuration des Menus</a>
                <span class="description">Permet de convertir les pages de menus en image JPG.</span>

            </div>
        </section>
    </div>
</body>

<!------------FOOTER------------>
<?php include "../modules/footer.php"; ?>
